//  未ログインの場合の警告スクリプト
function showAlert(){
    alert('コメントするにはログインが必要です');
    return false;
}

// avatar画像プレビュー
     // avatar画像プレビュー
     function previewAvatar(event) {
        const input = event.target;
        const preview = document.getElementById('avatar-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                // プレビュー用の画像のsrcをセット
                preview.src = e.target.result;
                // hiddenクラスを削除して表示
                preview.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            // ファイルが選択されていない場合、プレビューを隠す
            preview.src = "#";
            preview.classList.add('hidden');
        }
    }
