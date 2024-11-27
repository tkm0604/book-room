import './bootstrap';
import { createApp } from 'vue';
import PostForm from './components/PostForm.vue'; // 作成するVueコンポーネント

// PostFormをルートコンポーネントとして#appにマウント
createApp(PostForm).mount('#app');
