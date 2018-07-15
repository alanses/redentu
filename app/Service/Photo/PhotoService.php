<?php

namespace App\Services\Photo;

use Illuminate\Http\Request;

use Intervention\Image\Facades\Image;
use App\Entity\Image as ModelImage;


Class PhotoService
{
    static $directories;
    static $textWaterMark;

    protected $originImage;
    protected $resultImage;
    protected $watermarkImage;
    protected $weight;
    protected $height;

    public function __construct()
    {
        self::createDir(['origin', 'watermark', 'result']);
        $this->originImage = '';
        $this->resultImage = '';
        $this->weight = '';
        $this->height = '';
    }

    private function getFullPathToResultDirectory($image)
    {
        return self::$directories['result'] . DIRECTORY_SEPARATOR . str_random(8) . '_' . $image->getClientOriginalName();
    }

    private function getFullPathToOriginDirectory($image)
    {
        return self::$directories['origin'] . DIRECTORY_SEPARATOR . str_random(8) . '_' . $image->getClientOriginalName();
    }


    public function Resize($request, $image = null, Array $size = null)
    {
        if (!$size) {
            $size = self::getSizeImageForResize($request);
        }
        if ($image) {
            $img = Image::make($image);
            $pathToImage = $this->getFullPathToResultDirectory($this->originImage);
        } else {
            $fileMainImage = $this->originImage;
            $pathToImage = $this->getFullPathToResultDirectory($fileMainImage);
            $img = Image::make($fileMainImage);
        }

        $img->heighten($size['height'], function ($constraint) {
            $constraint->upsize();
        })->save($pathToImage);

        $img->widen($size['weight'], function ($constraint) {
            $constraint->upsize();
        })->save($pathToImage);

        return $img;
    }

    public function makeWaterMarkWithImage($mainImage, $waterImage)
    {
        $watermark = Image::make($waterImage);
        $originImage = Image::make($mainImage);

        $watermarkSize = ($originImage->width() - 20) / 2;

        $resizePercentage = 70;

        $watermarkSize = round($originImage->width() * ((100 - $resizePercentage) / 100), 2);

        $watermark->resize($watermarkSize, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $originImage->insert($watermark, 'center');
        $originImage->save($this->getFullPathToResultDirectory($mainImage));
        $this->saveToDB($this->getFullPathToResultDirectory($mainImage));

        return $originImage;
    }

    public function makeWaterMarkWithText($mainImage, $waterMarkText)
    {
        $image = Image::make($mainImage);
        $image->text($waterMarkText, 450, 450, function ($font) use ($mainImage) {
            $font->file(public_path('/fonts/Xerox Serif Wide.ttf'));
            $font->size(60);
            $font->color($this->get_avg_luminance($mainImage));
            $font->align('center');
            $font->valign('top');
            $font->angle(45);
        });

        $image->save($this->getFullPathToResultDirectory($mainImage));
        $this->saveToDB($this->getFullPathToResultDirectory($mainImage));
        return $image;

    }


    protected function get_avg_luminance($mainImage, $num_samples = 10)
    {
        $image = Image::make($mainImage);
        $path = $this->getFullPathToOriginDirectory($mainImage);
        $image->save($path);

        $img = \imagecreatefromjpeg($path);
        $x_step = \intval($image->width() / $num_samples);
        $y_step = \intval($image->height() / $num_samples);

        $total_lum = 0;
        $sample_no = 1;

        for ($x = 0; $x < $image->width(); $x += $x_step) {
            for ($y = 0; $y < $image->height(); $y += $y_step) {

                $rgb = \imagecolorat($img, $x, $y);
                $r = (int)($rgb >> 16) & 0xFF;
                $g = (int)($rgb >> 8) & 0xFF;
                $b = (int)$rgb & 0xFF;
                $lum = ($r + $r + $b + $g + $g + $g) / 6;

                $total_lum += $lum;

                $sample_no++;
            }
        }
        $avg_lum = $total_lum / $sample_no;
        if ($avg_lum > 170) {
            return [0, 0, 0, 1];
        } else {
            return [255,255,255, 0.9];
        }
    }

    public function validateRequest(Request $request)
    {
        $this->setImages($request);
        $this->setParamImages($request);

        if ($this->originImage && self::$textWaterMark) {
            return $this->getArrayResult($this->makePhotoWithWaterMarkText($request));
        }

        if ($this->originImage && $this->watermarkImage) {
            return $this->getArrayResult($this->makePhotoWithWaterMarkImage($request));
        }

        return false;

    }


    static function getSizeImageForResize(Request $request)
    {
        return [
            'weight' => $request->get('weight'),
            'height' => $request->get('height')
        ];
    }

    static function createDir(Array $directories)
    {
        try {
            \array_map(function ($dir) {
                if (!file_exists(public_path($dir))) {
                    mkdir(public_path($dir), 0777, true);
                }
                return self::$directories[$dir] = public_path($dir);

            }, $directories);

            return true;

        } catch (\Exception $exception) {
            return self::$directories = $exception->getMessage();
        }
    }

    protected function saveToDB($image)
    {
        return ModelImage::updateOrCreate([
            'name' => $image
        ]);
    }

    protected function makePhotoWithWaterMarkText(Request $request)
    {
        $imageWithWaterMarkText = $this->makeWaterMarkWithText($this->originImage, self::$textWaterMark);
        if ($this->height && $this->weight) {
            $resizeImage = $this->Resize($request, $imageWithWaterMarkText);
            return $resizeImage;
        }
        return $imageWithWaterMarkText;
    }

    protected function makePhotoWithWaterMarkImage(Request $request)
    {
        $imageWithWaterMarkImage = $this->makeWaterMarkWithImage($this->originImage, $this->watermarkImage);
        if ($this->height && $this->weight) {
            $resizeImage = $this->Resize($request, $imageWithWaterMarkImage);
            return $resizeImage;
        }
        return $imageWithWaterMarkImage;
    }

    protected function getArrayResult($result)
    {
        return [
            'dirname' => $result->dirname,
            'basename' => $result->basename,
            'extension' => $result->extension,
            'filename' => $result->filename,
        ];
    }

    protected function setImages(Request $request)
    {
        $this->originImage = $request->file('mainImage');
        $this->watermarkImage = $request->file('watermark');
    }

    protected function setParamImages(Request $request)
    {
        $this->height = $request->get('height');
        $this->weight = $request->get('weight');

        self::$textWaterMark = $request->get('textForWaterMark');
    }
}
