<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
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
    public function store(Request $request)
    {

        $input = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max_mb_chars:130', //Xへの投稿の文字数制限
            'image' =>  ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], //画像のバリデーション
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

        return redirect()->route('post.create')->with('message', '投稿を作成しました');
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

        // レート制限の状況を確認 (画像アップロードの前)
        $rateLimitStatus = $twitter->get('application/rate_limit_status', ['resources' => 'media']);
        Log::info('画像アップロードに関するレート制限状況: ' . json_encode($rateLimitStatus));

        if (isset($rateLimitStatus->resources->media->{'media/upload'}) && $rateLimitStatus->resources->media->{'media/upload'}->remaining === 0) {
            Log::error('画像アップロードのレート制限に達しています。リクエストを中止します。');
            return false;
        }

        $mediaId = null;

        // 画像をTwitterへアップロードしてメディアIDを取得
        try {
            // S3から画像を取得して一時ファイルに保存
            $tempImage = tempnam(sys_get_temp_dir(), 'tweet_image');
            file_put_contents($tempImage, file_get_contents($imagePath));

            // ファイルが存在し、読み取り可能であることを確認
            if (!is_readable($tempImage)) {
                Log::error('一時ファイルが存在しないか読み取りできません: ' . $tempImage);
                return false;
            }

            // 画像をアップロードしてメディアIDを取得
            $media = $twitter->upload('media/upload', ['media' => $tempImage]);

            // HTTPコードとレスポンスを取得
            $uploadHttpCode = $twitter->getLastHttpCode();
            $lastBody = $twitter->getLastBody();

            // エラーチェック
            if ($uploadHttpCode !== 200 || !$media) {
                Log::error('画像のアップロードに失敗しました。HTTPコード: ' . $uploadHttpCode . ' レスポンス: ' . json_encode($lastBody));
                return false;
            }

            $mediaId = $media->media_id_string ?? null;

            if (!$mediaId) {
                Log::error('メディアIDが取得できませんでした。アップロード結果: ' . json_encode($media));
                return false;
            }
        } catch (\Exception $e) {
            Log::error('例外が発生しました。エラー: ' . $e->getMessage());
            return false;
        } finally {
            if ($tempImage && file_exists($tempImage)) {
                unlink($tempImage);
            }
        }

        // レート制限の状況を確認 (ツイートの投稿前)
        $rateLimitStatus = $twitter->get('application/rate_limit_status', ['resources' => 'statuses']);
        Log::info('ツイートに関するレート制限状況: ' . json_encode($rateLimitStatus));

        if (isset($rateLimitStatus->resources->statuses->{'statuses/update'}) && $rateLimitStatus->resources->statuses->{'statuses/update'}->remaining === 0) {
            Log::error('ツイートのレート制限に達しています。リクエストを中止します。');
            return false;
        }

        // v2用のTwitterOAuthインスタンスを新しく作成
        $twitterV2 = new TwitterOAuth(
            env('TWITTER_CLIENT_ID'),
            env('TWITTER_CLIENT_SECRET'),
            $user->twitter_token,
            $user->twitter_token_secret
        );

        // v2エンドポイントのツイート内容
        $twitterContent = [
            "text" => $title . "\n" . $body,
            'media' => [
                'media_ids' => [$mediaId]
            ]
        ];

        // Twitterに投稿 (v2 エンドポイント)
        try {
            // Twitterに投稿 (v2 エンドポイント)
            $response = $twitterV2->post('tweets', $twitterContent);
            $httpCode = $twitterV2->getLastHttpCode();

            if ($httpCode === 201) {
                Log::info('ツイートに成功しました: ' . json_encode($response));
                $tweetId = $response->data->id ?? null; // ツイートIDを取得
                if ($tweetId) {
                    Log::info('取得したツイートID: ' . $tweetId);
                    return $tweetId; // 正しく取得したツイートIDを返す
                } else {
                    Log::error('ツイートIDがレスポンスに含まれていません。レスポンス: ' . json_encode($response));
                }
            } else {
                Log::error('ツイートに失敗しました。HTTPコード: ' . $httpCode . ' エラーメッセージ: ' . json_encode($twitterV2->getLastBody()));
            }
        } catch (\Exception $e) {
            Log::error('例外が発生しました。エラー: ' . $e->getMessage());
        }

        return false; // ツイートIDを取得できなかった場合
    }



    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('post.show', compact('post'));
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
    public function update(Request $request, Post $post)
    {
        // ポリシーを使用して認可
        if ($request->user()->cannot('update', $post)) {
            abort(403);
        }

        $input = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max_mb_chars:130', //Xへの投稿の文字数制限
            'image' =>  ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], //画像のバリデーション
        ]);

        $post->title = $input['title'];
        $post->body = $input['body'];

        if ($request->file('image')) {
            $original = $request->file('image')->getClientOriginalName(); //画像の名前を取得
            $name = date('YmdHis') . '_' . $original; //画像の名前を変更
            // S3に画像をアップロード
            $path = request()->file('image')->storeAs('images/post_images', $name, 's3');
            // S3のURLを取得してDBに保存
            $url = Storage::disk('s3')->url($path);

            // 古い画像を削除
            if ($post->image) {
                // S3のURLを画像パスに変換
                $oldImagePath = parse_url($post->image, PHP_URL_PATH); //パスを抽出
                $oldImagePath = ltrim($oldImagePath, '/'); // パスの先頭にスラッシュがあれば削除
                // 古い画像をS3から削除
                Storage::disk('s3')->delete($oldImagePath);
            }

            // 新しい画像のURLを保存
            $post->image = $url;
        }
        //投稿を保存
        $post->save();

        //正しい歯像パスを渡してTwitterに投稿
        // $this->postTweet($post->title, $post->body, $imagePath);

        return redirect()->route('post.show', $post)->with('message', '投稿を更新しました');
    }



    // private function deleteTweet($tweetId)
    // {
    //     if (!$tweetId) {
    //         Log::warning('ツイートIDが指定されていません。削除処理をスキップします。');
    //         return false;
    //     }

    //     $user = auth()->user();

    //     // TwitterOAuthのインスタンスを作成
    //     $twitter = new TwitterOAuth(
    //         env('TWITTER_CLIENT_ID'),
    //         env('TWITTER_CLIENT_SECRET'),
    //         $user->twitter_token,
    //         $user->twitter_token_secret
    //     );

    //     // ツイートの存在確認
    //     try {
    //         $response = $twitter->get("statuses/show", ['id' => $tweetId]);
    //         if ($twitter->getLastHttpCode() !== 200) {
    //             Log::warning('削除対象のツイートが存在しません: ' . $tweetId);
    //             return false;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('ツイート存在確認中に例外が発生しました。エラー: ' . $e->getMessage());
    //         return false;
    //     }

    //     // ツイート削除リクエスト
    //     try {
    //         $response = $twitter->post("statuses/destroy/$tweetId", []); // 削除リクエスト
    //         $httpCode = $twitter->getLastHttpCode();

    //         if ($httpCode === 200) {
    //             Log::info('ツイート削除に成功しました: ' . json_encode($response));
    //             return true;
    //         } else {
    //             Log::error('ツイート削除に失敗しました。HTTPコード: ' . $httpCode . ' レスポンス: ' . json_encode($twitter->getLastBody()));
    //             return false;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('ツイート削除中に例外が発生しました。エラー: ' . $e->getMessage());
    //         return false;
    //     }
    // }



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
        $post->comments()->delete();
        $post->delete();
        return redirect()->route('home.index')->with('message', '投稿を削除しました');
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
