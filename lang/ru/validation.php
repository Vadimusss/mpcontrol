<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Поле :attribute должно быть принято.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute должно быть действительным URL.',
    'after' => 'Поле :attribute должно быть датой после :date.',
    'after_or_equal' => 'Поле :attribute должно быть датой после или равной :date.',
    'alpha' => 'Поле :attribute должно содержать только буквы.',
    'alpha_dash' => 'Поле :attribute должно содержать только буквы, цифры, дефисы и подчеркивания.',
    'alpha_num' => 'Поле :attribute должно содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'ascii' => 'Поле :attribute должно содержать только однобайтовые буквенно-цифровые символы.',
    'before' => 'Поле :attribute должно быть датой до :date.',
    'before_or_equal' => 'Поле :attribute должно быть датой до или равной :date.',
    'between' => [
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
        'file' => 'Поле :attribute должно быть от :min до :max килобайт.',
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'string' => 'Поле :attribute должно быть от :min до :max символов.',
    ],
    'boolean' => 'Поле :attribute должно быть true или false.',
    'can' => 'Поле :attribute содержит неавторизованное значение.',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'contains' => 'Поле :attribute не содержит требуемого значения.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute должно быть корректной датой.',
    'date_equals' => 'Поле :attribute должно быть датой, равной :date.',
    'date_format' => 'Поле :attribute должно соответствовать формату :format.',
    'decimal' => 'Поле :attribute должно содержать :decimal знаков после запятой.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поля :attribute и :other должны различаться.',
    'digits' => 'Поле :attribute должно содержать :digits цифр.',
    'digits_between' => 'Поле :attribute должно содержать от :min до :max цифр.',
    'dimensions' => 'Поле :attribute имеет недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'doesnt_end_with' => 'Поле :attribute не должно заканчиваться на: :values.',
    'doesnt_start_with' => 'Поле :attribute не должно начинаться с: :values.',
    'email' => 'Поле :attribute должно быть действительным email адресом.',
    'ends_with' => 'Поле :attribute должно заканчиваться на: :values.',
    'enum' => 'Выбранное значение для :attribute недопустимо.',
    'exists' => 'Выбранное значение для :attribute недопустимо.',
    'extensions' => 'Поле :attribute должно иметь одно из следующих расширений: :values.',
    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле :attribute должно иметь значение.',
    'gt' => [
        'array' => 'Поле :attribute должно содержать более :value элементов.',
        'file' => 'Поле :attribute должно быть больше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'string' => 'Поле :attribute должно быть длиннее :value символов.',
    ],
    'gte' => [
        'array' => 'Поле :attribute должно содержать :value элементов или более.',
        'file' => 'Поле :attribute должно быть больше или равно :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше или равно :value.',
        'string' => 'Поле :attribute должно быть длиной :value символов или более.',
    ],
    'hex_color' => 'Поле :attribute должно быть корректным HEX-цветом.',
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение для :attribute недопустимо.',
    'in_array' => 'Поле :attribute должно существовать в :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно быть действительным IP-адресом.',
    'ipv4' => 'Поле :attribute должно быть действительным IPv4 адресом.',
    'ipv6' => 'Поле :attribute должно быть действительным IPv6 адресом.',
    'json' => 'Поле :attribute должно быть корректной JSON строкой.',
    'list' => 'Поле :attribute должно быть списком.',
    'lowercase' => 'Поле :attribute должно быть в нижнем регистре.',
    'lt' => [
        'array' => 'Поле :attribute должно содержать менее :value элементов.',
        'file' => 'Поле :attribute должно быть меньше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'string' => 'Поле :attribute должно быть короче :value символов.',
    ],
    'lte' => [
        'array' => 'Поле :attribute должно содержать не более :value элементов.',
        'file' => 'Поле :attribute должно быть меньше или равно :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше или равно :value.',
        'string' => 'Поле :attribute должно быть короче или равно :value символов.',
    ],
    'mac_address' => 'Поле :attribute должно быть действительным MAC-адресом.',
    'max' => [
        'array' => 'Поле :attribute не должно содержать более :max элементов.',
        'file' => 'Поле :attribute не должно быть больше :max килобайт.',
        'numeric' => 'Поле :attribute не должно быть больше :max.',
        'string' => 'Поле :attribute не должно быть длиннее :max символов.',
    ],
    'max_digits' => 'Поле :attribute не должно содержать более :max цифр.',
    'mimes' => 'Поле :attribute должно быть файлом типа: :values.',
    'mimetypes' => 'Поле :attribute должно быть файлом типа: :values.',
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Поле :attribute должно быть не менее :min килобайт.',
        'numeric' => 'Поле :attribute должно быть не менее :min.',
        'string' => 'Поле :attribute должно быть не короче :min символов.',
    ],
    'min_digits' => 'Поле :attribute должно содержать не менее :min цифр.',
    'missing' => 'Поле :attribute должно отсутствовать.',
    'missing_if' => 'Поле :attribute должно отсутствовать, когда :other равно :value.',
    'missing_unless' => 'Поле :attribute должно отсутствовать, если :other не равно :value.',
    'missing_with' => 'Поле :attribute должно отсутствовать при наличии :values.',
    'missing_with_all' => 'Поле :attribute должно отсутствовать при наличии всех :values.',
    'multiple_of' => 'Поле :attribute должно быть кратным :value.',
    'not_in' => 'Выбранное значение для :attribute недопустимо.',
    'not_regex' => 'Формат поля :attribute недопустим.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать хотя бы одну заглавную и одну строчную букву.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одну цифру.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Значение :attribute обнаружено в утечке данных. Пожалуйста, выберите другое значение.',
    ],
    'present' => 'Поле :attribute должно присутствовать.',
    'present_if' => 'Поле :attribute должно присутствовать, когда :other равно :value.',
    'present_unless' => 'Поле :attribute должно присутствовать, если :other не равно :value.',
    'present_with' => 'Поле :attribute должно присутствовать при наличии :values.',
    'present_with_all' => 'Поле :attribute должно присутствовать при наличии всех :values.',
    'prohibited' => 'Поле :attribute запрещено.',
    'prohibited_if' => 'Поле :attribute запрещено, когда :other равно :value.',
    'prohibited_unless' => 'Поле :attribute запрещено, если :other не входит в :values.',
    'prohibits' => 'Поле :attribute запрещает присутствие :other.',
    'regex' => 'Формат поля :attribute недопустим.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_array_keys' => 'Поле :attribute должно содержать записи для: :values.',
    'required_if' => 'Поле :attribute обязательно, когда :other равно :value.',
    'required_if_accepted' => 'Поле :attribute обязательно, когда :other принято.',
    'required_if_declined' => 'Поле :attribute обязательно, когда :other отклонено.',
    'required_unless' => 'Поле :attribute обязательно, если :other не входит в :values.',
    'required_with' => 'Поле :attribute обязательно при наличии :values.',
    'required_with_all' => 'Поле :attribute обязательно при наличии всех :values.',
    'required_without' => 'Поле :attribute обязательно при отсутствии :values.',
    'required_without_all' => 'Поле :attribute обязательно при отсутствии всех :values.',
    'same' => 'Поля :attribute и :other должны совпадать.',
    'size' => [
        'array' => 'Поле :attribute должно содержать :size элементов.',
        'file' => 'Поле :attribute должно быть :size килобайт.',
        'numeric' => 'Поле :attribute должно быть :size.',
        'string' => 'Поле :attribute должно быть длиной :size символов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с: :values.',
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно быть действительным часовым поясом.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Загрузка поля :attribute не удалась.',
    'uppercase' => 'Поле :attribute должно быть в верхнем регистре.',
    'url' => 'Поле :attribute должно быть действительным URL.',
    'ulid' => 'Поле :attribute должно быть действительным ULID.',
    'uuid' => 'Поле :attribute должно быть действительным UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'settings.commission' => [
            'required' => 'Введите число',
        ],
        'settings.logistics' => [
            'required' => 'Введите число',
        ],
        'settings.percentile_coefficient' => [
            'required' => 'Введите число',
        ],
        'settings.weight_coefficient' => [
            'required' => 'Введите число',
        ],
        'settings.gsheet_url' => [
            'required' => 'Ссылка на Google таблицу с НСИ обязательно для заполнения.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
