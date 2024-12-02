<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sortOrder = request()->query('sort', 'desc');
        $posts = Post::orderBy('created_at', $sortOrder)->paginate(6);
        $user = auth()->user();
        return view('index', compact('posts', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'title' => 'required|string|max:50', // 最大50文字に変更（Xへの投稿の文字数制限）
            'body' => 'required|string|max:150', // 最大150文字に変更（Xへの投稿の文字数制限）
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // フォーマットとサイズ制限
        ]);


        $post = new Post();

        $post->title = $request->title;
        $post->body = $request->body;
        $post->user_id = auth()->user()->id;
        //画像の名前を取得
        $original = $request->file('image')->getClientOriginalName();
        //画像の名前を変更
        $name = date('YmdHis') . '_' . $original;

        // S3に画像をアップロード
        $path = request()->file('image')->storeAs('images/post_images', $name, 's3');
        // S3のURLを取得してDBに保存
        $url = Storage::disk('s3')->url($path);
        $post->image = $url;

        // Postを保存
        $post->save();

        // 正しい画像パスを渡してTwitterに投稿
        $tweetId = $this->postTweet($post->title, $post->body, $url);
        Log::info('取得したツイートID: ' . json_encode($tweetId));

        // ツイートIDが返ってきた場合、それをPostモデルに保存
        if ($tweetId) {
            $post->tweet_id = $tweetId;
            if ($post->save()) {
                Log::info('ツイートIDが正常に保存されました。');
            } else {
                Log::error('ツイートIDの保存に失敗しました。');
            }
        }

        return response()->json([
            'redirect' => route('post.mypost'),
            'message' => '投稿を作成しました',
        ], 200);
    }

    /**
     * Xへの投稿処理関数
     */
    public function postTweet($title, $body, $imagePath)
    {
        $user = auth()->user();

        // 認証情報を取得し、TwitterOAuthのインスタンスを作成
        $twitter = new TwitterOAuth(
            env('TWITTER_CLIENT_ID'),
            env('TWITTER_CLIENT_SECRET'),
            $user->twitter_token,
            $user->twitter_token_secret
        );

        // APIバージョンを1.1に設定
        $twitter->setApiVersion('1.1');

        // レート制限を確認（ログとして保持）
        $rateLimit = $twitter->get('application/rate_limit_status');
        Log::info('レート制限状況: ' . json_encode($rateLimit));

        $mediaId = null;

        // 画像をTwitterへアップロードしてメディアIDを取得
        try {
            $tempImage = tempnam(sys_get_temp_dir(), 'tweet_image');
            file_put_contents($tempImage, file_get_contents($imagePath));

            if (!is_readable($tempImage)) {
                Log::error('一時ファイルが読み取れません: ' . $tempImage);
                return false;
            }

            $media = $twitter->upload('media/upload', ['media' => $tempImage]);
            $uploadHttpCode = $twitter->getLastHttpCode();

            Log::info('画像アップロードのHTTPステータス: ' . $uploadHttpCode);
            Log::info('アップロードされたメディアID: ' . json_encode($media));

            if ($uploadHttpCode !== 200 || !$media) {
                Log::error('画像アップロードが失敗しました。HTTPステータス: ' . $uploadHttpCode);
                return false;
            }

            $mediaId = $media->media_id_string ?? null;
            if (!$mediaId) {
                Log::error('メディアIDが取得できません');
                return false;
            }
        } catch (\Exception $e) {
            Log::error('画像アップロード中に例外発生: ' . $e->getMessage());
            return false;
        } finally {
            if ($tempImage && file_exists($tempImage)) {
                unlink($tempImage);
            }
        }

        // v2用のTwitterOAuthインスタンスを新しく作成
        $twitterV2 = new TwitterOAuth(
            env('TWITTER_CLIENT_ID'),
            env('TWITTER_CLIENT_SECRET'),
            $user->twitter_token,
            $user->twitter_token_secret
        );

        $twitterContent = [
            "text" => $title . "\n" . $body,
            'media' => [
                'media_ids' => [$mediaId]
            ]
        ];

        try {
            $response = $twitterV2->post('tweets', $twitterContent);
            $httpCode = $twitterV2->getLastHttpCode();

            // レート制限ヘッダーを取得
            $rateLimitHeaders = $twitterV2->getLastXHeaders();
            Log::info('ツイート投稿HTTPステータス: ' . $httpCode);
            Log::info('レート制限ヘッダー: ' . json_encode($rateLimitHeaders));

            if ($httpCode === 201) {
                $tweetId = $response->data->id ?? null;
                Log::info('ツイート作成成功: ' . $tweetId);
                return $tweetId;
            } else {
                Log::error('ツイート作成失敗: HTTPステータスコード ' . $httpCode);
            }
        } catch (\Exception $e) {
            Log::error('ツイート投稿中に例外発生: ' . $e->getMessage());
            return false;
        }

        return false;
    }



    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //セッションに保存されているキーviewed_postsの値を取得
        $viewedPosts = session()->get('viewed_posts', []);

        // 投稿したユーザー以外、かつ未カウントの場合のみカウント
        if(auth()->id() !== $post->user_id && !in_array($post->id, $viewedPosts)) {
            $post->increment('viewcount');
            session()->push('viewed_posts', $post->id);
        }

        return view('post.show', compact('post'));
    }

    /**
    *投稿の編集画面ではJSON形式でデータを返す
    */
    public function showApi($id)
    {
        $post = Post::findOrFail($id); // 投稿を取得
        return response()->json($post); // JSON形式で返す
    }


    /**
     * Update the specified resource in storage.
     */

    public function updateApi(Request $request, $id)
    {
        try {
            // 投稿を取得
            $post = Post::findOrFail($id);
            Log::info("取得した投稿: " . json_encode($post->toArray()));

            // ポリシーを使用して認可
            if ($request->user()->cannot('update', $post)) {
                abort(403);
            }

            // バリデーション
            $validated = $request->validate([
                'title' => 'required|string|max:50',
                'body' => 'required|string|max:150',
                'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            ]);

            // タイトルと本文の更新
            $post->title = $validated['title'];
            $post->body = $validated['body'];

            // 画像がアップロードされた場合の処理
            if ($request->file('image')) {
                $original = $request->file('image')->getClientOriginalName();
                $name = date('YmdHis') . '_' . $original;
                $path = $request->file('image')->storeAs('images/post_images', $name, 's3');
                $url = Storage::disk('s3')->url($path);

                if ($post->image) {
                    $oldImagePath = parse_url($post->image, PHP_URL_PATH);
                    if ($oldImagePath) {
                        $oldImagePath = ltrim($oldImagePath, '/');
                        Storage::disk('s3')->delete($oldImagePath);
                    } else {
                        Log::warning("古い画像のパスが取得できませんでした: {$post->image}");
                    }
                }

                $post->image = $url;
            }

            $post->save();

            // JSONレスポンスを返す
            return response()->json(['message' => '投稿が更新されました'], 200);
        } catch (\Exception $e) {
            Log::error('投稿更新中に例外が発生しました: ' . $e->getMessage());
            return response()->json(['error' => '投稿の更新中にエラーが発生しました。'], 500);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post)
    {
        // ポリシーを使用して認可
        if ($request->user()->cannot('update', $post)) {
            abort(403);
        }

        return view('post.edit', compact('post'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($request->user()->cannot('delete', $post)) {
            abort(403);
        }

        // S3に画像が存在する場合、削除する
        if ($post->image) {
            // S3のURLを画像パスに変換
            $imagePath = parse_url($post->image, PHP_URL_PATH);
            $imagePath = ltrim($imagePath, '/'); // パスの先頭にスラッシュがあれば削除
            // 画像をS3から削除
            Storage::disk('s3')->delete($imagePath);
        }

        $post->comments()->delete();
        $post->delete();

        return redirect()->route('home')->with('message', '投稿を削除しました');
    }


    public function mypost()
    {
        $sortOrder = request()->query('sort', 'desc');
        $user = auth()->user();
        $posts = Post::where('user_id', $user->id)->orderBy('created_at', $sortOrder)->paginate(6);
        return view('post.mypost', compact('posts'));
    }

    public function mycomment()
    {
        $sortOrder = request()->query('sort', 'desc');
        $user = auth()->user();
        $comments = Comment::where('user_id', $user->id)->orderBy('created_at', $sortOrder)->paginate(6);
        return view('post.mycomment', compact('comments'));
    }
}
