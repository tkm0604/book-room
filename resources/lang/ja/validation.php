<?php
return [
    'required' => ':attribute は必須項目です。',
    'unique' => ':attribute はすでに使用されています。',
    'confirmed' => ':attribute が確認欄と一致しません。',

    'max' => [
        'string' => ':attribute は :max 文字以内で入力してください。',
        'file' => ':attribute は :max MB 以下でなければなりません。',
    ],

    'min' => [
        'numeric' => ':attribute は :min 以上でなければなりません。',
        'file' => ':attribute は :min KB以上でなければなりません。',
        'string' => ':attribute は :min 文字以上で入力してください。',
        'array' => ':attribute は :min 個以上でなければなりません。',
    ],

    'password' => [
        'min' => ':attribute は :min 文字以上で入力してください。',
        'mixed' => ':attribute には大文字と小文字の両方を含める必要があります。',
        'uncompromised' => '指定された :attribute は漏洩している可能性があり、使用できません。',
    ],

    'image' => ':attribute には有効な画像ファイルを指定してください。',
    'mimes' => ':attribute の形式が正しくありません。:values 形式のファイルを指定してください。',

    'attributes' => [
        'title' => 'タイトル',
        'body' => '本文',
        'image' => '画像',
        'avatar' => 'アバター画像',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
    ],
    
    'custom' => [
        'avatar' => [
            'max' => 'アバター画像は 5 MB 以下でなければなりません。',
        ],
    ],
];
