import './bootstrap';
import { createApp } from 'vue';
import PostForm from './components/PostForm.vue'; // 作成するVueコンポーネント
import PostEdit from './components/PostEdit.vue'; // 投稿編集用

// アプリを作成してコンポーネントを登録
const app = createApp({});

// 各Vueコンポーネントを登録
app.component('post-form', PostForm);
app.component('post-edit', PostEdit);

// Vueアプリをマウント
app.mount('#app');
