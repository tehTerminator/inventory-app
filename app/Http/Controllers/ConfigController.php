<?php

namespace App\Http\Controllers;

class ConfigController extends Controller
{
    public static $path_of_config_file = 'app/config.json';
    public static $default_data = [
        'appName' => 'My App'
    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getAppName() {
        return response()->json(self::getKey('appName'));
    }

    public function setAppName(Request $request) {

    }

    private static function getKey($key) {
        if (!file_exists(self::$path_of_config_file)) {
            self::createDefaultConfigFile();
            return [$key => 'No Data Stored'];
        } else {
            $data = file_get_contents(self::$path_of_config_file);
            $jsonData = json_decode($data, TRUE);
            if (key_exists($key, $jsonData)); {
                return [$key => $jsonData[$key]];
            }
            return [$key => 'No Data Stored'];
        }
    }

    private static function storeKey($key) {
        $data = 
    }

    public function createDefaultConfigFile() {
        $file_path = storage_path(self::$path_of_config_file);
        $jsonData = json_encode(self::$default_data, JSON_PRETTY_PRINT);
        file_put_contents($file_path, $jsonData);
    }

    //
}
