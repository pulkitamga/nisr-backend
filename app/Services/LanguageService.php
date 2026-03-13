<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LanguageService
{
    private function normalizeLanguageItem(array $data): array
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'direction' => $data['direction'] ?? 'ltr',
            'code' => strtolower($data['code']),
            'country_code' => strtolower($data['country_code'] ?? $data['code']),
            'status' => $data['status'],
            'default' => (array_key_exists('default', $data) ? $data['default'] : ($data['code'] == 'en')),
        ];
    }

    public function getAddData(object $request, object $language): array
    {
        $languageArray = [];
        $codes = [];
        $newCode = strtolower($request['code']);
        $newCountryCode = strtolower($request['country_code']);

        foreach (json_decode($language['value'], true) as $data) {
            $normalized = $this->normalizeLanguageItem($data);
            $languageArray[] = $normalized;
            $codes[] = $normalized['code'];
        }

        $codes[] = $newCode;

        if (!file_exists(base_path('resources/lang/' . $newCode))) {
            mkdir(base_path('resources/lang/' . $newCode), 0777, true);
        }

        $messagesNewFile = fopen(base_path('resources/lang/' . $newCode . '/new-messages.php'), 'w') or die('Unable to open file!');
        fopen(base_path('resources/lang/' . $newCode . '/messages.php'), 'w') or die('Unable to open file!');
        $messagesFromDefaultLanguage = file_get_contents(base_path('resources/lang/en/messages.php'));

        fwrite($messagesNewFile, $messagesFromDefaultLanguage);
        $messagesFileContents = "<?php\n\nreturn [];\n";
        file_put_contents(base_path('resources/lang/' . $newCode . '/messages.php'), $messagesFileContents);

        $translatedMessagesArray = include(base_path('resources/lang/en/messages.php'));
        $newMessagesArray = include(base_path('resources/lang/en/new-messages.php'));
        $allMessages = array_merge($translatedMessagesArray, $newMessagesArray);
        $dataFiltered = [];
        foreach ($allMessages as $key => $value) {
            $dataFiltered[removeSpecialCharacters(text: $key)] = $value;
        }
        $string = "<?php return " . var_export($dataFiltered, true) . ";";
        file_put_contents(base_path('resources/lang/' . $newCode . '/new-messages.php'), $string);

        $languageValue = json_decode($language['value'], true);
        $languageCount = count($languageValue);
        $id = $languageValue[$languageCount - 1]['id'] + 1;

        $languageArray[] = [
            'id' => $id,
            'name' => $request['name'],
            'code' => $newCode,
            'country_code' => $newCountryCode,
            'direction' => $request['direction'],
            'status' => 0,
            'default' => false,
        ];

        session()->put('language', $languageArray);

        return [
            'languages' => $languageArray,
            'codes' => $codes,
        ];
    }

    public function getStatusData(object $request, object $language): array
    {
        $languageArray = [];
        $requestedCode = strtolower($request['code']);

        foreach (json_decode($language['value'], true) as $data) {
            $lang = $this->normalizeLanguageItem($data);
            if ($lang['code'] == $requestedCode) {
                $lang['status'] = $lang['status'] == 1 ? 0 : 1;
            }
            $languageArray[] = $lang;
        }

        return $languageArray;
    }

    public function getDefaultData(object $request, object $language): array
    {
        $languageArray = [];
        $requestedCode = strtolower($request['code']);

        foreach (json_decode($language['value'], true) as $data) {
            $lang = $this->normalizeLanguageItem($data);
            if ($lang['code'] == $requestedCode) {
                $lang['status'] = 1;
                $lang['default'] = true;
            } else {
                $lang['default'] = false;
            }
            $languageArray[] = $lang;
        }

        return $languageArray;
    }

    public function getUpdateData(object $request, object $language): array
    {
        $languageArray = [];
        $codes = [];
        $oldCode = strtolower($request['old_code'] ?? $request['code']);
        $newCode = strtolower($request['code']);
        $newCountryCode = strtolower($request['country_code']);

        if ($oldCode !== $newCode) {
            $oldPath = base_path('resources/lang/' . $oldCode);
            $newPath = base_path('resources/lang/' . $newCode);
            if (File::isDirectory($oldPath) && !File::isDirectory($newPath)) {
                File::moveDirectory($oldPath, $newPath);
            }
        }

        foreach (json_decode($language['value'], true) as $data) {
            $lang = $this->normalizeLanguageItem($data);

            if ($lang['code'] == $oldCode) {
                $lang['name'] = $request['name'];
                $lang['direction'] = $request['direction'] ?? 'ltr';
                $lang['code'] = $newCode;
                $lang['country_code'] = $newCountryCode;
            }

            $languageArray[] = $lang;
            $codes[] = $lang['code'];
        }

        session()->put('language', $languageArray);

        return [
            'languages' => $languageArray,
            'codes' => $codes,
        ];
    }

    public function getLangDelete(object $language, string $code): array
    {
        $del_default = false;
        foreach (json_decode($language['value'], true) as $data) {
            if ($data['code'] == $code && array_key_exists('default', $data) && $data['default']) {
                $del_default = true;
            }
        }

        $languageArray = [];
        foreach (json_decode($language['value'], true) as $data) {
            if ($data['code'] != $code) {
                $langData = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => strtolower($data['code']),
                    'country_code' => strtolower($data['country_code'] ?? $data['code']),
                    'status' => ($del_default && $data['code'] == 'en') ? 1 : $data['status'],
                    'default' => ($del_default && $data['code'] == 'en') ? true : (array_key_exists('default', $data) ? $data['default'] : $data['code'] == 'en'),
                ];
                $languageArray[] = $langData;
            }
        }

        $dir = base_path('resources/lang/' . $code);
        if (File::isDirectory($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }

        return $languageArray;
    }

    public function getTranslateList(string $language): array
    {
        $data = [];
        $path = base_path('resources/lang/' . $language . '/messages.php');
        if (File::exists($path)) {
            $newMessagesData = include(base_path('resources/lang/' . $language . '/new-messages.php'));
            $oldMessagesData = include(base_path('resources/lang/' . $language . '/messages.php'));
            ksort($newMessagesData);
            ksort($oldMessagesData);

            $index = 1;
            foreach ($newMessagesData as $key => $value) {
                $data[] = [
                    'index' => $index++,
                    'key' => $key,
                    'value' => $value,
                    'encode' => !empty($key) ? base64_encode($key) : '',
                ];
            }

            foreach ($oldMessagesData as $key => $value) {
                $data[] = [
                    'index' => $index++,
                    'key' => $key,
                    'value' => $value,
                    'encode' => !empty($key) ? base64_encode($key) : '',
                ];
            }
        }
        return $data;
    }

    public function getAllMessagesTranslateProcess(string $language, int $count = 999999999): array
    {
        $newMessagesArray = include(base_path('resources/lang/' . $language . '/new-messages.php'));
        $translatedMessagesArray = include(base_path('resources/lang/' . $language . '/messages.php'));
        $response = [
            'status' => 0,
            'message' => translate('Cannot_translate_now'),
            'due_message' => count($newMessagesArray),
        ];

        $translateCountSuccess = 0;
        $translateCount = 0;
        if ($newMessagesArray) {
            if (count($newMessagesArray) <= 0) {
                $response = ['status' => 1, 'message' => translate('All_Messages_are_translated'), 'translateCountSuccess' => $translateCountSuccess];
            }
            foreach ($newMessagesArray as $key => $value) {
                if ($translateCount < $count) {
                    $langCode = getLanguageCode($language);
                    $translated = autoTranslator($key, 'en', $langCode);
                    $translatedMessagesArray[$key] = removeSpecialCharacters($translated);
                    $translatedKey = $key;
                    $translateCountSuccess++;

                    $messagesFileContents = "<?php\n\nreturn [\n";
                    foreach ($translatedMessagesArray as $k => $tmaValue) {
                        $messagesFileContents .= "\t\"" . $k . "\" => \"" . $tmaValue . "\",\n";
                    }
                    $messagesFileContents .= "];\n";
                    file_put_contents(base_path('resources/lang/' . $language . '/messages.php'), $messagesFileContents);

                    $sourcePath = base_path('resources/lang/' . $language . '/new-messages.php');
                    $targetPath = base_path('resources/lang/' . $language . '/new-messages.php');
                    self::getAddTranslateNewKey($sourcePath, $targetPath, $translatedKey);
                    $translateCount++;
                    $response = [
                        'status' => 1,
                        'message' => translate('Translate_Successful'),
                        'due_message' => count(include(base_path('resources/lang/' . $language . '/new-messages.php'))),
                        'translateCountSuccess' => $translateCountSuccess,
                    ];
                }
            }
        } else {
            $response = [
                'status' => 1,
                'message' => translate('All_Messages_are_translated'),
                'due_message' => count(include(base_path('resources/lang/' . $language . '/new-messages.php'))),
                'translateCountSuccess' => $translateCountSuccess,
            ];
        }

        return $response;
    }

    public function getAddTranslateNewKey($sourcePath, $targetPath, $translatedKey): void
    {
        $getNewMessagesArray = include($sourcePath);
        $remainingMessagesFileContents = "<?php\n\nreturn [\n";
        foreach ($getNewMessagesArray as $newMsgKey => $newMsgValue) {
            if ($newMsgKey != $translatedKey) {
                $remainingMessagesFileContents .= "\t\"" . $newMsgKey . "\" => \"" . $newMsgValue . "\",\n";
            }
        }
        $remainingMessagesFileContents .= "];\n";
        file_put_contents($targetPath, $remainingMessagesFileContents);
    }
}
