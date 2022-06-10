<?php

namespace Crater\Space;

use Artisan;
use Crater\Events\ModuleEnabledEvent;
use Crater\Events\ModuleInstalledEvent;
use Crater\Http\Resources\ModuleResource;
use Crater\Models\Module as ModelsModule;
use Crater\Models\Setting;
use Exception;
use File;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Nwidart\Modules\Facades\Module;
use ZipArchive;

// Implementation taken from Akaunting - https://github.com/akaunting/akaunting
class ModuleInstaller
{
    use SiteApi;

	/**
	 * @throws GuzzleException
	 */
	public static function getModules()
    {
        $data = null;
        if (config('app.env') === 'development') {
            $url = 'api/marketplace/modules?is_dev=1';
        } else {
            $url = 'api/marketplace/modules';
        }

        $token = Setting::getSetting('api_token');
        $response = static::getRemote($url, ['timeout' => 100, 'track_redirects' => true], $token);

        if ($response && ($response->getStatusCode() == 401)) {
            return response()->json(['error' => 'invalid_token']);
        }

        if ($response && ($response->getStatusCode() == 200)) {
            $data = $response->getBody()->getContents();
        }

        $data = json_decode($data);

        return ModuleResource::collection(collect($data->modules));
    }

	/**
	 * @throws GuzzleException
	 */
	public static function getModule($module)
    {
        $data = null;
        if (config('app.env') === 'development') {
            $url = 'api/marketplace/modules/'.$module.'?is_dev=1';
        } else {
            $url = 'api/marketplace/modules/'.$module;
        }

        $token = Setting::getSetting('api_token');
        $response = static::getRemote($url, ['timeout' => 100, 'track_redirects' => true], $token);

        if ($response && ($response->getStatusCode() == 401)) {
            return (object)['success' => false, 'error' => 'invalid_token'];
        }

        if ($response && ($response->getStatusCode() == 200)) {
            $data = $response->getBody()->getContents();
        }

		return json_decode($data);
    }

    public static function upload($request)
    {
        // Create temp directory
        $temp_dir = storage_path('app/temp-'.md5(mt_rand()));

        if (! File::isDirectory($temp_dir)) {
            File::makeDirectory($temp_dir);
        }

		return $request->file('avatar')->storeAs(
			'temp-'.md5(mt_rand()),
			$request->module.'.zip',
			'local'
		);
    }

	/**
	 * @throws GuzzleException
	 */
	public static function download($module, $version)
    {
        $data = null;
        $path = null;

        if (config('app.env') === 'development') {
            $url = "api/marketplace/modules/file/{$module}?version={$version}&is_dev=1";
        } else {
            $url = "api/marketplace/modules/file/{$module}?version={$version}";
        }

        $token = Setting::getSetting('api_token');
        $response = static::getRemote($url, ['timeout' => 100, 'track_redirects' => true], $token);

        // Exception
        if ($response instanceof RequestException) {
            return [
                'success' => false,
                'error' => 'Download Exception',
                'data' => [
                    'path' => $path,
                ],
            ];
        }

        if ($response && ($response->getStatusCode() == 401 || $response->getStatusCode() == 404 || $response->getStatusCode() == 500)) {
            return json_decode($response->getBody()->getContents());
        }

        if ($response && ($response->getStatusCode() == 200)) {
            $data = $response->getBody()->getContents();
        }

        // Create temp directory
        $temp_dir = storage_path('app/temp-'.md5(mt_rand()));

        if (! File::isDirectory($temp_dir)) {
            File::makeDirectory($temp_dir);
        }

        $zip_file_path = $temp_dir.'/upload.zip';

        // Add content to the Zip file
        $uploaded = is_int(file_put_contents($zip_file_path, $data));

        if (! $uploaded) {
            return false;
        }

        return [
            'success' => true,
            'path' => $zip_file_path
        ];
    }

	/**
	 * @throws Exception
	 */
	public static function unzip($module, $zip_file_path): string
	{
        if (! file_exists($zip_file_path)) {
            throw new Exception('Zip file not found');
        }

        $temp_extract_dir = storage_path('app/temp2-'.md5(mt_rand()));

        if (! File::isDirectory($temp_extract_dir)) {
            File::makeDirectory($temp_extract_dir);
        }
        // Unzip the file
        $zip = new ZipArchive();

        if ($zip->open($zip_file_path)) {
            $zip->extractTo($temp_extract_dir);
        }

        $zip->close();

        // Delete zip file
        File::delete($zip_file_path);

        return $temp_extract_dir;
    }

    public static function copyFiles($module, $temp_extract_dir): bool
	{
        if (! File::isDirectory(base_path('Modules'))) {
            File::makeDirectory(base_path('Modules'));
        }

        // Delete Existing Module directory
        if (! File::isDirectory(base_path('Modules').'/'.$module)) {
            File::deleteDirectory(base_path('Modules').'/'.$module);
        }

        if (! File::copyDirectory($temp_extract_dir, base_path('Modules').'/')) {
            return false;
        }

        // Delete temp directory
        File::deleteDirectory($temp_extract_dir);

        return true;
    }

    public static function deleteFiles($json): bool
	{
        $files = json_decode($json);

        foreach ($files as $file) {
            \File::delete(base_path($file));
        }

        return true;
    }

    public static function complete($module, $version): bool
	{
        Module::register();

        Artisan::call("module:migrate $module --force");
        Artisan::call("module:seed $module --force");
        Artisan::call("module:enable $module");

        $module = ModelsModule::updateOrCreate(['name' => $module], ['version' => $version, 'installed' => true, 'enabled' => true]);

        ModuleInstalledEvent::dispatch($module);
        ModuleEnabledEvent::dispatch($module);

        return true;
    }

	/**
	 * @throws GuzzleException
	 */
	public static function checkToken(String $token): JsonResponse
	{
        $url = 'api/marketplace/ping';

        $response = static::getRemote($url, ['timeout' => 100, 'track_redirects' => true], $token);

        if ($response && ($response->getStatusCode() == 200)) {
            $data = $response->getBody()->getContents();

            return response()->json(json_decode($data));
        }

        return response()->json(['error' => 'invalid_token']);
    }
}
