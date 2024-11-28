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
          @input="validateField('title')"
          :class="{'border-red-500': errors.title}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          placeholder="本のタイトルを入力してください"
        />
        <p v-if="errors.title" class="text-red-500">{{ errors.title }}</p>
        <p class="text-sm text-gray-500">文字数: {{ post.title.length }}/50</p>
      </div>

      <!-- 本文入力 -->
      <div class="mb-4">
        <label for="body" class="block text-sm font-medium text-gray-700">本文</label>
        <textarea
          id="body"
          v-model="post.body"
          @input="validateField('body')"
          :class="{'border-red-500': errors.body}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          rows="5"
          placeholder="この本の感想やおすすめポイントを書いてください"
        ></textarea>
        <p class="text-sm text-gray-500">文字数: {{ post.body.length }}/150</p>
        <p v-if="errors.body" class="text-red-500">{{ errors.body }}</p>
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
          :class="{'border-red-500': errors.image}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
        />
        <p v-if="errors.image" class="text-red-500">{{ errors.image }}</p>
      </div>

      <!-- 更新ボタン -->
      <button
        type="submit"
        :disabled="hasErrors || isUpdating"
        :style="{
            width: '100%',
            backgroundColor: isUpdating ? '#d1d5db' : '#3b82f6',
            color: 'white',
            padding: '8px 16px',
            borderRadius: '4px',
            fontWeight: 'bold',
        }">
        {{ isUpdating ? '更新中...' : '更新する' }}
        </button>

      <p v-if="hasErrors" class="text-red-500 text-center mt-4">未入力の項目があります。入力内容を確認してください。</p>
    </form>
  </template>

  <script>
  export default {
    props: {
      postId: { type: Number, required: true },
    },
    data() {
      return {
        post: {
          title: '',
          body: '',
          image: null,
        },
        imagePreview: null,
        errors: {
          title: null,
          body: null,
          image: null,
        },
        isUpdating: false, // 更新ボタンの送信状態を管理
      };
    },
    computed: {
      hasErrors() {
        return Object.values(this.errors).some((error) => error !== null);
      },
    },
    mounted() {
      this.fetchPost();
    },
    methods: {
      async fetchPost() {
        try {
          const response = await fetch(`/api/posts/${this.postId}`);
          if (!response.ok) throw new Error('投稿データの取得に失敗しました');
          this.post = await response.json();
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
          this.imagePreview = URL.createObjectURL(file);

          if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/jpg')) {
            this.errors.image = '画像フォーマットはJPEG, PNG, JPGのみです';
          } else if (file.size > 5120 * 1024) {
            this.errors.image = '画像サイズは5MB以内である必要があります';
          } else {
            this.errors.image = null;
          }
        }
      },
      validateField(field) {
        if (field === 'title') {
          if (!this.post.title) {
            this.errors.title = 'タイトルは必須です';
          } else if (this.post.title.length > 50) {
            this.errors.title = 'タイトルは50文字以内で入力してください';
          } else {
            this.errors.title = null;
          }
        } else if (field === 'body') {
          if (!this.post.body) {
            this.errors.body = '本文は必須です';
          } else if (this.post.body.length > 150) {
            this.errors.body = '本文は150文字以内で入力してください';
          } else {
            this.errors.body = null;
          }
        }
      },
      async updatePost() {
              // 更新ボタンを無効化
      this.isUpdating = true;

  this.validateField('title');
  this.validateField('body');
  this.validateField('image');

  if (this.hasErrors) return;

  const formData = new FormData();
  formData.append('title', this.post.title);
  formData.append('body', this.post.body);
  if (this.post.image instanceof File) {
    formData.append('image', this.post.image);
  }
  formData.append('_method', 'PATCH'); // サーバー側でPATCHとして認識させる

  try {
    const response = await fetch(`/api/posts/${this.postId}`, {
      method: 'POST', // 実際のリクエストはPOST
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: formData,
    });

    if (!response.ok) throw new Error('投稿の更新に失敗しました');

        alert('投稿が更新されました');
        window.location.href = '/post/mypost';
    } catch (error) {
        console.error(error);
        alert('更新中にエラーが発生しました');
    }
    },
    },
  };
  </script>

  <style>
  .text-red-500 {
    color: #f56565;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  .border-red-500 {
    border-color: #f56565;
  }
  </style>
