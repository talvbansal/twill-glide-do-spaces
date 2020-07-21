<?php

namespace Talvbansal\TwillGlideDoSpaces\Services\MediaLibrary;

use A17\Twill\Services\MediaLibrary\ImageServiceDefaults;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Urls\UrlBuilderFactory;

class Glide extends \A17\Twill\Services\MediaLibrary\Glide
{
    use ImageServiceDefaults;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \League\Glide\Server
     */
    private $server;

    /**
     * @var \League\Glide\Urls\UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param Config $config
     * @param Application $app
     * @param Request $request
     */
    public function __construct(Config $config, Application $app, Request $request)
    {
        $this->config = $config;
        $this->app = $app;
        $this->request = $request;

        // The original code here only used the disk endpoint and didn't generate the FQDN from the endpoint for s3 compatible drivers...
        $diskConfig = config('filesystems.disks.twill_media_library');
        if ($diskConfig['driver'] === 's3') {
            $parts = parse_url($diskConfig['endpoint']);
            $baseUrl = sprintf("%s://%s.%s", $parts['scheme'], $diskConfig['bucket'], $parts['host']);
        } else {
            $baseUrlHost = $this->config->get(
                'twill.glide.base_url',
                $this->request->getScheme() . '://' . str_replace(
                    ['http://', 'https://'],
                    '',
                    $this->config->get('app.url')
                )
            );

            $baseUrl = join('/', [
                rtrim($baseUrlHost, '/'),
                ltrim($this->config->get('twill.glide.base_path'), '/'),
            ]);
        }

        $this->server = ServerFactory::create([
            'response' => new LaravelResponseFactory($this->request),
            'source' => $this->config->get('twill.glide.source'),
            'cache' => $this->config->get('twill.glide.cache'),
            'cache_path_prefix' => $this->config->get('twill.glide.cache_path_prefix'),
            'base_url' => $baseUrl,
            'presets' => $this->config->get('twill.glide.presets', []),
            'driver' => $this->config->get('twill.glide.driver'),
        ]);

        $this->urlBuilder = UrlBuilderFactory::create(
            $baseUrl,
            $this->config->get('twill.glide.use_signed_urls') ? $this->config->get('twill.glide.sign_key') : null
        );
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function render($path)
    {
        if ($this->config->get('twill.glide.use_signed_urls')) {
            SignatureFactory::create($this->config->get('twill.glide.sign_key'))->validateRequest($this->config->get('twill.glide.base_path') . '/' . $path, $this->request->all());
        }

        return $this->server->getImageResponse($path, $this->request->all());
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getUrl($id, array $params = [])
    {
        $defaultParams = config('twill.glide.default_params');
        $addParamsToSvgs = config('twill.glide.add_params_to_svgs', false);

        if (! $addParamsToSvgs && Str::endsWith($id, '.svg')) {
            return $this->urlBuilder->getUrl($id);
        }

        return $this->urlBuilder->getUrl($id, array_replace($defaultParams, $params));
    }

    /**
     * @param string $id
     * @param array $cropParams
     * @param array $params
     * @return string
     */
    public function getUrlWithCrop($id, array $cropParams, array $params = [])
    {
        return $this->getUrl($id, $this->getCrop($cropParams) + $params);
    }

    /**
     * @param string $id
     * @param array $cropParams
     * @param mixed $width
     * @param mixed $height
     * @param array $params
     * @return string
     */
    public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = [])
    {
        return $this->getUrl($id, $this->getFocalPointCrop($cropParams, $width, $height) + $params);
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getLQIPUrl($id, array $params = [])
    {
        $defaultParams = config('twill.glide.lqip_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getSocialUrl($id, array $params = [])
    {
        $defaultParams = config('twill.glide.social_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getCmsUrl($id, array $params = [])
    {
        $defaultParams = config('twill.glide.cms_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id, string $preset
     * @return string
     */
    public function getPresetUrl($id, $preset)
    {
        return $this->getRawUrl($id) . '?p=' . $preset;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRawUrl($id)
    {
        return $this->urlBuilder->getUrL($id);
    }

    /**
     * @param string $id
     * @return array
     */
    public function getDimensions($id)
    {
        $url = $this->urlBuilder->getUrL($id);

        try {
            list($w, $h) = getimagesize($url);

            return [
                'width' => $w,
                'height' => $h,
            ];
        } catch (\Exception $e) {
            return [
                'width' => 0,
                'height' => 0,
            ];
        }
    }
}
