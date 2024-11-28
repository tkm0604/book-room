<template>
    <form @submit.prevent="submitPost" class="max-w-lg mx-auto bg-white p-6 rounded shadow-md">
      <h2 class="text-2xl font-bold mb-4 text-center">読書記録を投稿する</h2>
      <!-- タイトル入力 -->
      <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
        <input
          type="text"
          id="title"
          v-model="title"
          @input="validateField('title')"
          :class="{'border-red-500': errors.title}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          placeholder="本のタイトルを入力してください"
        />
        <p v-if="errors.title" class="text-red-500">{{ errors.title }}</p>
        <p class="text-sm text-gray-500">文字数: {{ title.length }}/50</p>
      </div>

      <!-- 本文入力 -->
      <div class="mb-4">
        <label for="body" class="block text-sm font-medium text-gray-700">本文</label>
        <textarea
          id="body"
          v-model="body"
          @input="validateField('body')"
          :class="{'border-red-500': errors.body}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
          rows="5"
          placeholder="この本の感想やおすすめポイントを書いてください"
        ></textarea>
        <p class="text-sm text-gray-500">文字数: {{ body.length }}/150</p>
        <p v-if="errors.body" class="text-red-500">{{ errors.body }}</p>
      </div>

      <!-- 画像プレビュー -->
      <div v-if="imagePreview" class="mt-4">
        <img :src="imagePreview" alt="画像プレビュー" style="max-width: 100%; max-height: 300px;" />
      </div>

      <!-- 画像アップロード -->
      <div class="mb-4">
        <label for="image" class="block text-sm font-medium text-gray-700">本の表紙画像</label>
        <input
          type="file"
          id="image"
          @change="handleFileUpload"
          :class="{'border-red-500': errors.image}"
          class="block w-full mt-1 p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
        />
        <p v-if="errors.image" class="text-red-500">{{ errors.image }}</p>
      </div>

      <!-- 投稿ボタン -->
      <!-- <button
        type="submit"
        :disabled="hasErrors"
        style="width: 100%; background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 4px; font-weight: bold;">
        投稿する
      </button> -->


<button
  type="submit"
  :disabled="hasErrors || isSubmitting"
  :style="{
    width: '100%',
    backgroundColor: isSubmitting ? '#d1d5db' : '#3b82f6',
    color: 'white',
    padding: '8px 16px',
    borderRadius: '4px',
    fontWeight: 'bold',
  }">
  {{ isSubmitting ? '投稿中...' : '投稿する' }}
</button>


      <p v-if="hasErrors" class="text-red-500 text-center mt-4">未入力の項目があります。入力内容を確認してください。</p>
    </form>
  </template>

<script>
export default {
  data() {
    return {
      title: '',
      body: '',
      image: null,
      imagePreview: null,
      errors: {
        title: null,
        body: null,
        image: null,
      },
      isSubmitting: false, // ボタンの送信状態を管理
    };
  },
  computed: {
    hasErrors() {
      return Object.values(this.errors).some((error) => error !== null);
    },
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      this.image = file;

      if (file) {
        // プレビュー用のURLを作成
        this.imagePreview = URL.createObjectURL(file);

        // ファイルタイプが指定された形式か確認
        if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/jpg')) {
          this.errors.image = '画像フォーマットはJPEG, PNG, JPGのみです';
        } else if (file.size > 5120 * 1024) { // 5MB
          this.errors.image = '画像サイズは5MB以内である必要があります';
        } else {
          this.errors.image = null;
        }
      }
    },
    validateField(field) {
      if (field === 'title') {
        if (!this.title) {
          this.errors.title = 'タイトルは必須です';
        } else if (this.title.length > 50) {
          this.errors.title = 'タイトルは50文字以内で入力してください';
        } else {
          this.errors.title = null;
        }
      } else if (field === 'body') {
        if (!this.body) {
          this.errors.body = '本文は必須です';
        } else if (this.body.length > 150) {
          this.errors.body = '本文は150文字以内で入力してください';
        } else {
          this.errors.body = null;
        }
      } else if (field === 'image') {
        if (!this.image) {
          this.errors.image = '画像は必須です';
        } else if (!this.image.type.match('image/jpeg') && !this.image.type.match('image/png') && !this.image.type.match('image/jpg')) {
          this.errors.image = '画像フォーマットはJPEG, PNG, JPGのみです';
        } else if (this.image.size > 5120 * 1024) {
          this.errors.image = '画像サイズは5MB以内である必要があります';
        } else {
          this.errors.image = null;
        }
      }
    },

    submitPost() {
      // 本文末尾に#book-roomを追加
      let finalBody = this.body;
      if (this.body && !this.body.includes("#book-room")) {
        finalBody += ' #bookroom';  // 本文に自動的に#book-roomを追加
      }

            // ボタンを無効化
            this.isSubmitting = true;

      // バリデーション実行
      this.validateField('title');
      this.validateField('body');
      this.validateField('image');

      if (this.hasErrors) {
        return;
      }

      // フォームデータの作成
      const formData = new FormData();
      formData.append('title', this.title);
      formData.append('body', finalBody);
      if (this.image) {
        formData.append('image', this.image);
      }

      // 投稿の送信
      fetch('/post', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('サーバーエラーが発生しました');
          }
          return response.json();
        })
        .then((data) => {
          alert('投稿が完了しました');
          this.title = '';
          this.body = '';
          this.image = null;
          this.imagePreview = null;
          window.location.href = '/post/mypost'; // 投稿後にリダイレクト
        })
        .catch((error) => {
          console.error('投稿に失敗しました', error);
        });
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
