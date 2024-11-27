<template>
    <form @submit.prevent="updatePost" class="max-w-lg mx-auto bg-white p-6 rounded shadow-md">
      <h2 class="text-2xl font-bold mb-4 text-center">投稿を編集する</h2>

      <!-- タイトル入力 -->
      <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
        <input
          type="text"
          id="title"
          v-model="post.title"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          placeholder="本のタイトルを入力してください"
        />
      </div>

      <!-- 本文入力 -->
      <div class="mb-4">
        <label for="body" class="block text-sm font-medium text-gray-700">本文</label>
        <textarea
          id="body"
          v-model="post.body"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          rows="5"
          placeholder="この本の感想やおすすめポイントを書いてください"
        ></textarea>
      </div>

      <!-- 画像プレビュー -->
      <div v-if="imagePreview" class="mb-4">
        <img :src="imagePreview" alt="画像プレビュー" class="w-full max-h-60 object-contain" />
      </div>

      <!-- 画像アップロード -->
      <div class="mb-4">
        <label for="image" class="block text-sm font-medium text-gray-700">画像</label>
        <input
          type="file"
          id="image"
          @change="handleFileUpload"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <!-- 更新ボタン -->
      <button
        type="submit"
        class="w-full bg-blue-500 text-white p-2 rounded font-bold hover:bg-blue-700"
      >
        更新する
      </button>
    </form>
  </template>

  <script>
  export default {
    props: {
      postId: { type: Number, required: true }, // 投稿IDを受け取る
    },
    data() {
      return {
        post: {
          title: '',
          body: '',
          image: null,
        },
        imagePreview: null, // プレビュー用画像
      };
    },
    mounted() {
      this.fetchPost(); // 投稿データを取得
    },
    methods: {
      async fetchPost() {
        try {
          const response = await fetch(`/api/posts/${this.postId}`); // APIリクエスト
          if (!response.ok) throw new Error('投稿データの取得に失敗しました');

          // 取得した投稿データをpostに格納
          this.post = await response.json();

          // 画像が存在する場合はプレビュー用URLを設定
          if (this.post.image) {
            this.imagePreview = this.post.image;
          }
        } catch (error) {
          console.error(error);
          alert('投稿データの取得中にエラーが発生しました');
        }
      },
      handleFileUpload(event) {
        const file = event.target.files[0];
        this.post.image = file;

        if (file) {
          this.imagePreview = URL.createObjectURL(file); // 新しいプレビューURLを生成
        }
      },
      async updatePost() {
  const formData = new FormData();
  formData.append('title', this.post.title || ''); // 空の値を送信
  formData.append('body', this.post.body || '');

  // 新しい画像が選択されている場合のみ送信
  if (this.post.image instanceof File) {
    formData.append('image', this.post.image);
  }

  formData.append('_method', 'PATCH'); // LaravelでPATCHとして認識させる

  try {
    const response = await fetch(`/api/posts/${this.postId}`, {
      method: 'POST', // 実際のリクエストはPOST
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: formData,
    });

    console.log('FormData:', [...formData.entries()]); // デバッグ用ログ
    if (!response.ok) throw new Error('投稿の更新に失敗しました');

    alert('投稿が更新されました');
    window.location.href = '/post/mypost'; // 更新後リダイレクト
  } catch (error) {
    console.error(error);
    alert('更新中にエラーが発生しました');
  }
}
,
    },
  };
  </script>
