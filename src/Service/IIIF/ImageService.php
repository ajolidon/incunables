<?php

namespace App\Service\IIIF;


use App\Entity\Scan;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    /**
     * @var \Imagick
     */
    protected $image;

    /**
     * @var string
     */
    protected $publicPath;

    public function __construct(KernelInterface $kernel)
    {
        $this->publicPath = $kernel->getProjectDir() . '/public/';
    }


    /**
     * @param int $id
     * @param string $region    If the request specifies a region which extends beyond the dimensions reported in the
     *                          image information document, then the service returns an image cropped at the image’s edge.
     *                          If the requested region’s height or width is zero, or if the region is entirely outside
     *                          the bounds of the reported dimensions, then the server returns a 400 status code.
     *
     *          Possible values:
     *
     *          `full`          The complete image is returned, without any cropping.
     *          `square`        The region is defined as an area where the width and height are both equal to the length
     *                          of the shorter dimension of the complete image. The region is centered in the longer
     *                          dimension of the image content.
     *          `x,y,w,h`       The region of the full image to be returned is specified in terms of absolute pixel
     *                          values. The value of x represents the number of pixels from the 0 position on the
     *                          horizontal axis. The value of y represents the number of pixels from the 0 position on
     *                          the vertical axis. Thus the x,y position 0,0 is the upper left-most pixel of the image.
     *                          w represents the width of the region and h represents the height of the region in pixels.
     *          `pct:x,y,w,h`   The region to be returned is specified as a sequence of percentages of the full image’s
     *                          dimensions, as reported in the image information document. Thus, x represents the number
     *                          of pixels from the 0 position on the horizontal axis, calculated as a percentage of the
     *                          reported width. w represents the width of the region, also calculated as a percentage of
     *                          the reported width. The same applies to y and h respectively. These may be floating point
     *                          numbers.
     *
     * @param string $size      If the resulting height or width is zero, then the server returns a 400 status code.
     *                          The image server supports scaling above the full size of the extracted region.
     *
     *          Possible values:
     *
     *          `full`          The image or region is not scaled, and is returned at its full size.
     *          `max`           Same as `full`.
     *          `w,`            The image or region should be scaled so that its width is exactly equal to w, and the
     *                          height will be a calculated value that maintains the aspect ratio of the extracted
     *                          region.
     *          `,h`            The image or region should be scaled so that its height is exactly equal to h, and the
     *                          width will be a calculated value that maintains the aspect ratio of the extracted region.
     *          `pct:n`         The width and height of the returned image is scaled to n% of the width and height of
     *                          the extracted region. The aspect ratio of the returned image is the same as that of the
     *                          extracted region.
     *          `w,h`           The width and height of the returned image are exactly w and h. The aspect ratio of the
     *                          returned image may be different than the extracted region, resulting in a distorted
     *                          image.
     *          `!w,h`          The image content is scaled for the best fit such that the resulting width and height
     *                          are less than or equal to the requested width and height.
     *
     * @param string $rotation  A rotation value that is out of range results in a 400 status code.
     *
     *          Possible values:
     *
     *          `n`             The degrees of clockwise rotation from 0 up to 360.
     *          `!n`            The image content is left-to-right mirrored before rotation. `n` is the degrees of
     *                          clockwise rotation from 0 up to 360.
     *
     * @param string $quality   A quality value that is unsupported results in a 400 status code.
     *
     *          Possible values:
     *
     *          `color`         The image is returned in full color.
     *          `default`       Same as `color`.
     *
     * @param string $format    A format value that is unsupported results in a 400 status code.
     *
     *          Possible values:
     *
     *          `jpg`           image/jpeg
     *          `tif`           image/tiff
     *
     * @return string
     * @throws \Exception
     */
    public function createImage(Scan $scan, string $region, string $size, string $rotation, string $quality, string $format): string
    {
        $this->image = new \Imagick($this->publicPath . $scan->getPublicPath());

        if($rotation == 360){
            $rotation = 0;
        }

        if(!$this->isValidRegion($region)){
            throw new BadRequestHttpException('Region invalid.');
        }

        if(!$this->isValidSize($size)){
            throw new BadRequestHttpException('Size invalid.');
        }

        if(!$this->isValidRotation($rotation)){
            throw new BadRequestHttpException('Rotation invalid.');
        }

        if(!$this->isValidQuality($quality)){
            throw new BadRequestHttpException('Quality invalid.');
        }

        if(!$this->isValidFormat($format)){
            throw new BadRequestHttpException('Format invalid.');
        }

        $this->createRegion($region);
        $this->createSize($size);
        $this->createRotation($rotation);

        $this->image->setImageFormat('jpg');

        return $this->image->getImageBlob();
    }

    protected function createRegion($region)
    {
        $wOrig = $this->image->getImageWidth();
        $hOrig = $this->image->getImageHeight();
        $x = 0;
        $y = 0;

        if($region == 'square'){
            if($wOrig < $hOrig){
                $cutDistance = $wOrig;
                $y = floor(($hOrig - $wOrig) / 2);
            }else{
                $cutDistance = $hOrig;
                $x = floor(($wOrig - $hOrig) / 2);
            }

            $this->image->cropImage($cutDistance, $cutDistance, $x, $y);
        }elseif(strstr($region, ",")) {
            if (substr($region, 0, 4) == 'pct:') {
                $region = substr($region, 4);
                $arr = explode(",", $region);
                $x = round(($wOrig/100) * $arr[0]);
                $y = round(($hOrig/100) * $arr[1]);
                $w = round(($wOrig/100) * $arr[2]);
                $h = round(($hOrig/100) * $arr[3]);
            } else {
                $arr = explode(",", $region);
                $x = $arr[0];
                $y = $arr[1];
                $w = $arr[2];
                $h = $arr[3];
            }
            if ($x + $w > $this->image->getImageWidth()) {
                $w = $this->image->getImageWidth() - $x;
            }
            if ($y + $h > $this->image->getImageHeight()) {
                $h = $this->image->getImageWidth() - $y;
            }

            $this->image->cropImage($w, $h, $x, $y);
        }
    }

    protected function createSize($size){
        if($size == "full" || $size == "max"){
            return;
        }

        $wOrig = $this->image->getImageWidth();
        $hOrig = $this->image->getImageHeight();

        if(strstr($size, ',')){
            if(substr($size, 0, 1) != "!") {
                $arr = explode(",", $size);
                $newWidth = (int) $arr[0];
                $newHeight = (int) $arr[1];
                $this->image->scaleImage($newWidth, $newHeight);
            }else{
                $size = substr($size, 1);
                $arr = explode(",", $size);
                $newWidth = (int) $arr[0];
                $newHeight = (int) $arr[1];
                $ratio = $newWidth/$wOrig;
                if(round($hOrig * $ratio) <= $newHeight){
                    $this->image->scaleImage($newWidth, 0);
                }else{
                    $this->image->scaleImage(0, $newHeight);
                }
            }
        }else{
            $size = (int) substr($size, 4);
            $newWidth = round(($wOrig/100) * $size);
            $this->image->scaleImage($newWidth, 0);
        }
    }

    protected function createRotation($rotation)
    {
        if(substr($rotation, 0, 1) == "!"){
            $rotation = substr($rotation, 1);
            $this->image->flopImage();
        }

        $rotation = (float) $rotation;
        $this->image->rotateImage("rgb(255, 255, 255)", $rotation);
    }


    protected function isValidRegion(string &$region): bool
    {
        if(strstr($region, ",")){
            $arr = explode(",", $region);
            if(count($arr) != 4){
                return false;
            }

            $isPercentage = false;
            if(substr($arr[0], 0, 4) == 'pct:'){
                $arr[0] = substr($arr[0], 4);
                $isPercentage = true;
            }

            foreach($arr as $value){
                if(!is_numeric($value)){
                    return false;
                }
            }

            if($isPercentage){
                $arr[0] = $this->percentageToPixels($arr[0], $this->image->getImageWidth());
                $arr[1] = $this->percentageToPixels($arr[1], $this->image->getImageHeight());
                $arr[2] = $this->percentageToPixels($arr[2], $this->image->getImageWidth());
                $arr[3] = $this->percentageToPixels($arr[3], $this->image->getImageHeight());
            }

            $region = join(",", $arr);

            return $arr[2] > 0 && $arr[3] > 0
                && $arr[0] < $this->image->getImageWidth()
                && $arr[1] < $this->image->getImageHeight();
        }

        return $region == 'full' || $region == 'square';
    }

    protected function isValidSize(string $size): bool
    {
        if(strstr($size, ",")){
            $arr = explode(",", $size);
            if(count($arr) != 2){
                return false;
            }

            $needsValue = false;
            if(substr($arr[0], 0, 1) == "!"){
                $arr[0] = substr($arr[0], 1);
                $needsValue = true;
            }

            return strlen($arr[0]) + strlen($arr[1]) > 0
                && ((!$needsValue && strlen($arr[0]) == 0) || (strlen($arr[0]) > 0 && is_numeric($arr[0]) && $arr[0] > 0))
                && ((!$needsValue && strlen($arr[1]) == 0) || (strlen($arr[1]) > 0 && is_numeric($arr[1]) && $arr[1] > 0));
        }

        if(substr($size, 0, 4) == 'pct:'){
            return is_numeric(substr($size, 4));
        }

        return $size == 'full' || $size == 'max';
    }

    protected function isValidRotation(string $rotation): bool
    {
        if(substr($rotation, 0, 1) == "!"){
            $rotation = substr($rotation, 1);
        }

        return is_numeric($rotation) && $rotation >= 0 && $rotation <= 360;
    }


    protected function isValidQuality(string $quality): bool
    {
        return $quality == 'color' || $quality == 'default';
    }

    protected function isValidFormat(string $format): bool
    {
        return $format == 'jpg';
    }


    protected function percentageToPixels($percentage, $base){
        $percentage = $percentage / 100;

        if($percentage > 1){
            throw new BadRequestHttpException('Percentage greater than 100%.');
        }

        return round($base * $percentage);
    }
}