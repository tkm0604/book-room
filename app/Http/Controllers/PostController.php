<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
// use AWS\CRT\Log;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
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

    //  消すな！！
    // public function store(Request $request)
    // {

    //         バリデーション
    //         $input = $request->validate([
    //             'title' => 'required|string|max:50', // 最大50文字に変更（Xへの投稿の文字数制限）
    //             'body' => 'required|string|max:150', // 最大150文字に変更（Xへの投稿の文字数制限）
    //             'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // フォーマットとサイズ制限
    //         ]);


    //     $post = new Post();

    //     $post->title = $request->title;
    //     $post->body = $request->body;
    //     $post->user_id = auth()->user()->id;
    //     //画像の名前を取得
    //     $original = $request->file('image')->getClientOriginalName();
    //     //画像の名前を変更
    //     $name = date('YmdHis') . '_' . $original;

    //     // S3に画像をアップロード
    //     $path = request()->file('image')->storeAs('images/post_images', $name, 's3');
    //     // S3のURLを取得してDBに保存
    //     $url = Storage::disk('s3')->url($path);
    //     $post->image = $url;

    //     // Postを保存
    //     $post->save();

    //     // 正しい画像パスを渡してTwitterに投稿
    //     $tweetId = $this->postTweet($post->title, $post->body, $url);
    //     Log::info('取得したツイートID: ' . json_encode($tweetId));

    //     // ツイートIDが返ってきた場合、それをPostモデルに保存
    //     if ($tweetId) {
    //         $post->tweet_id = $tweetId;
    //         if ($post->save()) {
    //             Log::info('ツイートIDが正常に保存されました。');
    //         } else {
    //             Log::error('ツイートIDの保存に失敗しました。');
    //         }
    //     }

    //     return redirect()->route('post.create')->with('message', '投稿を作成しました');
    // }
    //消すなここまで

    public function store(Request $request)
    {

        try {
        // バリデーション
        $input = $request->validate([
            'title' => 'required|string|max:50', // 最大50文字に変更
            'body' => 'required|string|max:150', // 最大150文字に変更
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // フォーマットとサイズ制限
        ]);

            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            $post->user_id = auth()->user()->id;

            $original = $request->file('image')->getClientOriginalName();
            $name = date('YmdHis') . '_' . $original;
            $path = $request->file('image')->storeAs('images/post_images', $name, 's3');
            $url = Storage::disk('s3')->url($path);
            $post->image = $url;

            $post->save();

            Log::info('投稿が成功しました', $post->toArray());

            return response()->json([
                'message' => '投稿が成功しました！',
                'post' => $post,
            ], 200);

        } catch (\Exception $e) {
            Log::error('投稿エラー: ' . $e->getMessage());

            return response()->json([
                'message' => '投稿に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        return view('post.show', compact('post'));
    }


    public function showApi($id)
    {
        $post = Post::findOrFail($id); // 投稿を取得
        return response()->json($post); // JSON形式で返す
    }

    public function updateApi(Request $request, $id)
    {
        // 投稿を取得
        $post = Post::findOrFail($id);

        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // タイトルと本文の更新
        $post->title = $validated['title'];
        $post->body = $validated['body'];

        // 画像がアップロードされた場合の処理
        if ($request->file('image')) {
            $original = $request->file('image')->getClientOriginalName(); // 画像の元の名前を取得
            $name = date('YmdHis') . '_' . $original; // 名前を一意に変更
            $path = $request->file('image')->storeAs('images/post_images', $name, 's3'); // S3にアップロード
            $url = Storage::disk('s3')->url($path); // アップロードされた画像のURLを取得

            // 古い画像を削除
            if ($post->image) {
                $oldImagePath = parse_url($post->image, PHP_URL_PATH); // 古い画像のパスを抽出
                $oldImagePath = ltrim($oldImagePath, '/'); // 先頭のスラッシュを削除
                Storage::disk('s3')->delete($oldImagePath); // S3から古い画像を削除
            }

            // 新しい画像のURLを保存
            $post->image = $url;
        }

        // 投稿を保存
        $post->save();

        // JSONレスポンスを返す
        return response()->json(['message' => '投稿が更新されました'], 200);
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
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Post $post)
    // {
    //     // ポリシーを使用して認可
    //     if ($request->user()->cannot('update', $post)) {
    //         abort(403);
    //     }

    //     // 入力バリデーション
    //     $input = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'body' => 'required|string|max_mb_chars:130', // Xへの投稿の文字数制限
    //         'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // 画像のバリデーション
    //     ]);

    //     // タイトルと本文の更新
    //     $post->title = $input['title'];
    //     $post->body = $input['body'];

    //     // 画像の更新処理
    //     if ($request->file('image')) {
    //         $original = $request->file('image')->getClientOriginalName();
    //         $name = date('YmdHis') . '_' . $original;

    //         // S3に画像をアップロード
    //         $path = $request->file('image')->storeAs('images/post_images', $name, 's3');
    //         $url = Storage::disk('s3')->url($path);

    //         // 古い画像を削除
    //         if ($post->image) {
    //             $oldImagePath = ltrim(parse_url($post->image, PHP_URL_PATH), '/');
    //             Storage::disk('s3')->delete($oldImagePath);
    //         }

    //         // 新しい画像のURLをセット
    //         $post->image = $url;
    //     }

    //     // 古いツイートを削除
    //     if ($post->tweet_id) {
    //         Log::info("削除対象のツイートID: {$post->tweet_id}");
    //         if (!$this->deleteTweet($post->tweet_id)) {
    //             return redirect()->back()->with('error', 'ツイート削除に失敗しました。もう一度試してください。');
    //         }
    //         Log::info("ツイート削除に成功しました: {$post->tweet_id}");
    //     }

    //     // 新しいツイートを作成
    //     Log::info("新しいツイートを作成します: タイトル = {$post->title}, 本文 = {$post->body}");
    //     $newTweetId = $this->postTweet($post->title, $post->body, $post->image);

    //     if ($newTweetId) {
    //         $post->tweet_id = $newTweetId;
    //         Log::info("新しいツイートが作成されました: {$newTweetId}");
    //     } else {
    //         Log::error("新しいツイートの作成に失敗しました");
    //         return redirect()->back()->with('error', '新しいツイートの作成に失敗しました。');
    //     }

    //     // 投稿を保存
    //     if ($post->save()) {
    //         Log::info("投稿が正常に保存されました。投稿ID: {$post->id}");
    //     } else {
    //         Log::error("投稿の保存に失敗しました。投稿ID: {$post->id}");
    //         return redirect()->back()->with('error', '投稿の保存に失敗しました。');
    //     }

    //     return redirect()->route('post.show', $post)->with('message', '投稿を更新しました');
    // }

    private function deleteTweet($tweetId)
    {
        Log::info("削除対象のツイートID: " . $tweetId);

        if (!$tweetId) {
            Log::warning('ツイートIDが指定されていません。削除処理をスキップします。');
            return false;
        }

        $user = auth()->user();
        $twitter = new TwitterOAuth(
            env('TWITTER_CLIENT_ID'),
            env('TWITTER_CLIENT_SECRET'),
            $user->twitter_token,
            $user->twitter_token_secret
        );

        try {
            // ツイート削除リクエスト
            $twitter->post("statuses/destroy/$tweetId", []);
            $httpCode = $twitter->getLastHttpCode(); // TwitterOAuthオブジェクトから直接取得
            Log::info('削除リクエストHTTPコード: ' . $httpCode);

            if ($httpCode === 200) {
                Log::info("ツイート削除に成功しました: {$tweetId}");
                return true;
            } elseif ($httpCode === 404) {
                Log::warning("削除対象のツイートが存在しません（または既に削除済み）: {$tweetId}");
                return true; // すでに削除されている場合は成功として扱う
            } else {
                Log::error("ツイート削除に失敗しました。HTTPコード: {$httpCode}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("ツイート削除中に例外が発生しました。エラー: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
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


        // ツイート削除処理
        if ($post->tweet_id) {
            $deleteSuccess = $this->deleteTweet($post->tweet_id);
            if (!$deleteSuccess) {
                return redirect()->back()->with('error', 'ツイート削除に失敗しました。');
            }
        }


        $post->comments()->delete();
        $post->delete();


        Log::info("投稿が削除されました: ID = {$post->id}");

        return redirect()->route('home')->with('message', '投稿を削除しました');
    }


    public function mypost()
    {
        $user = auth()->user();
        $posts = Post::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('post.mypost', compact('posts'));
    }

    public function mycomment()
    {
        $user = auth()->user();
        $comments = Comment::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('post.mycomment', compact('comments'));
    }
}
