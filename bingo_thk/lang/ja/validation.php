<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | 以下の言語行には、バリデータクラスで使用されるデフォルトのエラーメッセージが含まれています。
    | これらのルールの一部には、サイズルールのように複数のバージョンがあります。
    | 必要に応じて、これらのメッセージを自由に調整してください。
    |
    */

    'accepted' => ':attribute を承認してください。',
    'accepted_if' => ':other が :value のとき、:attribute を承認してください。',
    'active_url' => ':attribute は有効なURLではありません。',
    'after' => ':attribute には :date 以降の日付を指定してください。',
    'after_or_equal' => ':attribute には :date 以降または同日の日付を指定してください。',
    'alpha' => ':attribute には文字のみを使用できます。',
    'alpha_dash' => ':attribute には英数字、ハイフン、アンダースコアのみ使用できます。',
    'alpha_num' => ':attribute には英数字のみを使用できます。',
    'array' => ':attribute は配列でなければなりません。',
    'ascii' => ':attribute には、半角英数字および記号のみを使用できます。',
    'before' => ':attribute には :date 以前の日付を指定してください。',
    'before_or_equal' => ':attribute には :date 以前または同日の日付を指定してください。',
    'between' => [
        'numeric' => ':attribute には :min から :max の間の値を指定してください。',
        'file' => ':attribute には :min から :max KBのファイルを指定してください。',
        'string' => ':attribute は :min から :max 文字の間で指定してください。',
        'array' => ':attribute の項目は :min から :max 個の間で指定してください。',
    ],
    'boolean' => ':attribute は true または false を指定してください。',
    'confirmed' => ':attribute の確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute は有効な日付ではありません。',
    'date_equals' => ':attribute には :date と同じ日付を指定してください。',
    'date_format' => ':attribute の形式は :format と一致しません。',
    'decimal' => ':attribute には :decimal 桁の小数を指定してください。',
    'declined' => ':attribute を拒否してください。',
    'different' => ':attribute と :other には異なる値を指定してください。',
    'digits' => ':attribute は :digits 桁でなければなりません。',
    'digits_between' => ':attribute は :min から :max 桁の間でなければなりません。',
    'dimensions' => ':attribute の画像サイズが無効です。',
    'distinct' => ':attribute に重複した値があります。',
    'email' => ':attribute には有効なメールアドレスを指定してください。',
    'ends_with' => ':attribute は次のいずれかで終わらなければなりません: :values。',
    'enum' => '選択された :attribute が無効です。',
    'exists' => '選択された :attribute は存在しません。',
    'file' => ':attribute はファイルでなければなりません。',
    'filled' => ':attribute は必須です。',
    'gt' => [
        'numeric' => ':attribute は :value より大きくなければなりません。',
        'file' => ':attribute は :value KBより大きくなければなりません。',
        'string' => ':attribute は :value 文字より長くなければなりません。',
        'array' => ':attribute には :value 個より多くの項目が必要です。',
    ],
    'gte' => [
        'numeric' => ':attribute は :value 以上でなければなりません。',
        'file' => ':attribute は :value KB以上でなければなりません。',
        'string' => ':attribute は :value 文字以上でなければなりません。',
        'array' => ':attribute には :value 個以上の項目が必要です。',
    ],
    'image' => ':attribute は画像でなければなりません。',
    'in' => '選択された :attribute は無効です。',
    'in_array' => ':attribute は :other に存在しません。',
    'integer' => ':attribute は整数でなければなりません。',
    'ip' => ':attribute は有効なIPアドレスでなければなりません。',
    'ipv4' => ':attribute は有効なIPv4アドレスでなければなりません。',
    'ipv6' => ':attribute は有効なIPv6アドレスでなければなりません。',
    'json' => ':attribute は有効なJSON文字列でなければなりません。',
    'lowercase' => ':attribute は小文字でなければなりません。',
    'lt' => [
        'numeric' => ':attribute は :value より小さくなければなりません。',
        'file' => ':attribute は :value KBより小さくなければなりません。',
        'string' => ':attribute は :value 文字より短くなければなりません。',
        'array' => ':attribute には :value 個未満の項目しか含められません。',
    ],
    'lte' => [
        'numeric' => ':attribute は :value 以下でなければなりません。',
        'file' => ':attribute は :value KB以下でなければなりません。',
        'string' => ':attribute は :value 文字以下でなければなりません。',
        'array' => ':attribute には :value 個以上の項目を含めることはできません。',
    ],
    'max' => [
        'numeric' => ':attribute は :max 以下でなければなりません。',
        'file' => ':attribute は :max KB以下でなければなりません。',
        'string' => ':attribute は :max 文字以下でなければなりません。',
        'array' => ':attribute には :max 個以下の項目しか含められません。',
    ],
    'mimes' => ':attribute は次の形式のファイルでなければなりません: :values。',
    'mimetypes' => ':attribute は次の形式のファイルでなければなりません: :values。',
    'min' => [
        'numeric' => ':attribute は少なくとも :min でなければなりません。',
        'file' => ':attribute は少なくとも :min KBでなければなりません。',
        'string' => ':attribute は少なくとも :min 文字でなければなりません。',
        'array' => ':attribute には少なくとも :min 個の項目が必要です。',
    ],
    'multiple_of' => ':attribute は :value の倍数でなければなりません。',
    'not_in' => '選択された :attribute は無効です。',
    'not_regex' => ':attribute の形式が無効です。',
    'numeric' => ':attribute は数値でなければなりません。',
    'password' => [
        'letters' => ':attribute には少なくとも1文字を含める必要があります。',
        'mixed' => ':attribute には大文字と小文字の両方を含める必要があります。',
        'numbers' => ':attribute には少なくとも1つの数字を含める必要があります。',
        'symbols' => ':attribute には少なくとも1つの記号を含める必要があります。',
        'uncompromised' => '指定された :attribute はデータ漏洩で見つかりました。別のパスワードを選択してください。',
    ],
    'present' => ':attribute は存在している必要があります。',
    'prohibited' => ':attribute フィールドは禁止されています。',
    'regex' => ':attribute の形式が無効です。',
    'required' => ':attribute は必須項目です。',
    'required_if' => ':other が :value の場合、:attribute は必須です。',
    'required_unless' => ':other が :values にない場合、:attribute は必須です。',
    'required_with' => ':values が存在する場合、:attribute は必須です。',
    'same' => ':attribute と :other は一致する必要があります。',
    'size' => [
        'numeric' => ':attribute は :size でなければなりません。',
        'file' => ':attribute は :size KBでなければなりません。',
        'string' => ':attribute は :size 文字でなければなりません。',
        'array' => ':attribute には :size 個の項目が必要です。',
    ],
    'starts_with' => ':attribute は次のいずれかで始まらなければなりません: :values。',
    'string' => ':attribute は文字列でなければなりません。',
    'timezone' => ':attribute は有効なタイムゾーンでなければなりません。',
    'unique' => ':attribute はすでに使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attribute は大文字でなければなりません。',
    'url' => ':attribute は有効なURLでなければなりません。',
    'uuid' => ':attribute は有効なUUIDでなければなりません。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | 以下の言語行を使用して、:attribute プレースホルダを人間に分かりやすい名前に置き換えることができます。
    | 例: 'email' => 'メールアドレス'
    |
    */

    'attributes' => [],

];
