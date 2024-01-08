<?php

/**
 * PixelCraft is a lightweight PHP library designed for easy image processing using the GD lib. 
 * With PixelCraft, you can perform basic image operations such as resizing, cropping, drawing of watermarks, and format conversion.
 * The library is characterized by its user-friendly interface and minimal code footprint, 
 * allowing for quick and efficient image processing in PHP projects.
 * 
 * @package PixelCraft
 * @author Rostyslav Haitkulov <info@ubilling.net.ua>
 * @see https://github.com/nightflyza/PixelCraft
 * @license MIT
 */
class PixelCraft
{

    /**
     * Contains image copy to perform some magic on it
     * 
     * @var GDimage
     */
    protected $image = '';

    /**
     * Contains image rendering quality
     * 
     * @var int
     */
    protected $quality = -1;

    /**
     * Contains loaded image mime type
     * 
     * @var string
     */
    protected $imageType = '';

    /**
     * Contains loaded image original width
     * 
     * @var int
     */
    protected $imageWidth = 0;
    /**
     * Contains loaded image original height
     * 
     * @var int
     */
    protected $imageHeight = 0;

    /**
     * Contains array of custom RGB colors palette as name=>color
     * 
     * @var array
     */
    protected $colorPalette = array();

    /**
     * Contains already allocated image colors as name=>colorInt
     * 
     * @var array
     */
    protected $colorsAllocated = array();

    /**
     * TTF font path
     * 
     * @var string
     */
    protected $font = 'skins/OpenSans-Regular.ttf';

    /**
     * Font size in pt.
     * 
     * @var int
     */
    protected $fontSize = 10;

    /**
     * Drawing line width in px
     * 
     * @var int
     */
    protected $lineWidth = 1;

    /**
     * Contains watermark image copy
     * 
     * @var GDimage
     */
    protected $watermark = '';

    /**
     * Schweigen im wald
     */
    public function __construct()
    {
        $this->setDefaultColors();
    }


    /**
     * Sets few default colors to palette
     * 
     * @return void
     */
    protected function setDefaultColors()
    {
        $this->addColor('white', 255, 255, 255);
        $this->addColor('black', 0, 0, 0);
        $this->addColor('red', 255, 0, 0);
        $this->addColor('blue', 0, 0, 255);
        $this->addColor('yellow', 255, 255, 0);
        $this->addColor('grey', 85, 85, 85);
    }

    /**
     * Sets current instance image save/render quality
     * 
     * @param int $quality Deafult -1 means IJG quality value for jpeg or default zlib for png
     * 
     * @return void
     */
    public function setQuality($quality)
    {
        if (is_int($quality)) {
            $this->quality = $quality;
        }
    }

    /**
     * Set TTF font path
     *
     * @param  string  $font  TTF font path
     *
     * @return  void
     */
    public function setFont($font)
    {
        $this->font = $font;
    }

    /**
     * Set font size in pt.
     *
     * @param  int  $fontSize  Font size in pt.
     *
     * @return  void
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
    }

    /**
     * Returns current image width
     * 
     * @return int
     */
    public function getImageWidth()
    {
        return ($this->imageWidth);
    }

    /**
     * Returns current image height
     * 
     * @return int
     */
    public function getImageHeight()
    {
        return ($this->imageHeight);
    }

    /**
     * Returns loaded image type
     * 
     * @return string
     */
    public function getImageType()
    {
        return ($this->imageType);
    }

    /**
     * Returns specified image 
     * 
     * @param string $filePath
     * 
     * @return array
     */
    protected function getImageParams($filePath)
    {
        return (@getimagesize($filePath));
    }

    /**
     * Creates or replaces RGB color in palette
     * 
     * @param string $colorName
     * @param int $r
     * @param int $g
     * @param int $b
     * 
     * @return void
     */
    public function addColor($colorName, $r, $g, $b)
    {
        $this->colorPalette[$colorName] = array('r' => $r, 'g' => $g, 'b' => $b);
    }


    /**
     * Checks is some image valid?
     * 
     * @param string $filePath
     * @param array $imageParams
     * 
     * @return bool
     */
    public function isImageValid($filePath = '', $imageParams = array())
    {
        $result = false;
        if (empty($imageParams) and !empty($filePath)) {
            $imageParams = $this->getImageParams($filePath);
        }

        if (is_array($imageParams)) {
            if (isset($imageParams['mime'])) {
                if (strpos($imageParams['mime'], 'image/') !== false) {
                    $result = true;
                }
            }
        }
        return ($result);
    }

    /**
     * Returns image type name
     * 
     * @param string $filePath
     * @param array $imageParams
     * 
     * @return string
     */
    protected function detectImageType($filePath = '', $imageParams = array())
    {
        $result = '';
        if ($this->isImageValid($filePath, $imageParams)) {
            if (empty($imageParams) and !empty($filePath)) {
                $imageParams = $this->getImageParams($filePath);
            }

            if (is_array($imageParams)) {
                $imageType = $imageParams['mime'];
                $imageType = str_replace('image/', '', $imageType);
                $result = $imageType;
            }
        }
        return ($result);
    }

    /**
     * Loads some image into protected property from file 
     * 
     * @param string $fileName readable image file path
     
     * @return bool
     */
    protected function loadImageFile($filePath, $propertyName = 'image')
    {
        $result = false;
        $imageParams = $this->getImageParams($filePath);
        $imageType = $this->detectImageType('', $imageParams);
        if (!empty($imageType)) {
            $loaderFunctionName = 'imagecreatefrom' . $imageType;
            if (function_exists($loaderFunctionName)) {
                $this->$propertyName = $loaderFunctionName($filePath);
                //setting loaded image base props
                if ($this->$propertyName != false) {
                    if ($propertyName == 'image') {
                        $this->imageWidth = $imageParams[0];
                        $this->imageHeight = $imageParams[1];
                        $this->imageType = $imageType;
                    }
                    $result = true;
                }
            } else {
                throw new Exception('EX_NOT_SUPPORTED_FILETYPE:' . $imageType);
            }
        }
        return ($result);
    }


    /**
     * Loads some image into protected property from file 
     * 
     * @param string $fileName readable image file path
     
     * @return bool
     */
    public function loadImage($filePath)
    {
        $result = $this->loadImageFile($filePath, 'image');
        return ($result);
    }

    /**
     * Loads some watermark image into protected property from file 
     * 
     * @param string $fileName readable image file path
     
     * @return bool
     */
    public function loadWatermark($filePath)
    {
        $result = $this->loadImageFile($filePath, 'watermark');
        if ($result) {
            imagealphablending($this->watermark, false);
        }
        return ($result);
    }


    /**
     * Renders current instance image into browser or specified file path
     * 
     * @param string $fileName writable file path with name or null to browser rendering
     * @param string $type image type to save, like jpeg, png, gif...
     * 
     * @return bool
     */
    public function saveImage($fileName = null, $type)
    {
        $result = false;
        if ($this->image) {
            $saveFunctionName = 'image' . $type;
            if (function_exists($saveFunctionName)) {
                //custom header on browser output
                if (!$fileName) {
                    header('Content-Type: image/' . $type);
                }
                if ($type == 'jpeg' or $type == 'png') {
                    if ($type == 'png') {
                        imagesavealpha($this->image, true);
                    }
                    $result = $saveFunctionName($this->image, $fileName, $this->quality);
                } else {
                    $result = $saveFunctionName($this->image, $fileName);
                }

                //memory free
                imagedestroy($this->image);

                //droppin image props
                $this->imageWidth = 0;
                $this->imageHeight = 0;
                $this->imageType = '';
                //nothing else matters
                if ($fileName === false) {
                    die();
                }
            } else {
                throw new Exception('EX_NOT_SUPPORTED_FILETYPE:' . $type);
            }
        } else {
            throw new Exception('EX_VOID_IMAGE');
        }
        return ($result);
    }

    /**
     * Creates new empty true-color image
     * 
     * @return void
     */
    public function createImage($width, $height)
    {
        $this->image = imagecreatetruecolor($width, $height);
        $this->imageWidth = $width;
        $this->imageHeight = $height;
    }


    /**
     * Scales image to some scale
     * 
     * @param float $scale something like 0.5 for 50% or 2 for 2x scale
     * 
     * @return void
     */
    public function scale($scale)
    {
        if ($this->imageWidth and $this->imageHeight) {
            if ($scale != 1) {
                $nWidth = $this->imageWidth * $scale;
                $nHeight = $this->imageHeight * $scale;
                $imageCopy = imagecreatetruecolor($nWidth, $nHeight);
                imagealphablending($imageCopy, false);
                imagesavealpha($imageCopy, true);
                imagecopyresized($imageCopy, $this->image, 0, 0, 0, 0, $nWidth, $nHeight, $this->imageWidth, $this->imageHeight);
                $this->image = $imageCopy;
                $this->imageWidth = $nWidth;
                $this->imageHeight = $nHeight;
            }
        }
    }

    /**
     * Resizes image to some new dimensions
     * 
     * @param int $width
     * @param int $height
     * 
     * @return void
     */
    public function resize($width, $height)
    {
        if ($this->imageWidth and $this->imageHeight) {
            $imageResized = imagescale($this->image, $width, $height);
            $this->image = $imageResized;
            $this->imageWidth = $width;
            $this->imageHeight = $height;
        }
    }


    /**
     * Allocates and returns some image color by its name
     * 
     * @param string $colorName
     * 
     * @return int
     */
    protected function allocateColor($colorName)
    {
        $result = 0;
        if (isset($this->colorPalette[$colorName])) {
            $colorData = $this->colorPalette[$colorName];
            if (isset($this->colorsAllocated[$colorName])) {
                $result = $this->colorsAllocated[$colorName];
            } else {
                $result = imagecolorallocate($this->image, $colorData['r'], $colorData['g'], $colorData['b']);
                $this->colorsAllocated[$colorName] = $result;
            }
        } else {
            throw new Exception('EX_COLOR_NOT_EXISTS:' . $colorName);
        }
        return ($result);
    }

    /**
     * Fills image with some color from palette 
     * 
     * @param string $colorName
     * 
     * @return void
     */
    public function fill($colorName)
    {
        imagefill($this->image, 0, 0,  $this->allocateColor($colorName));
    }

    /**
     * Draws pixel on X/Y coords with some color from palette
     * 
     * @param int y
     * @param int x
     * @param string $colorName
     * 
     * @return void
     */
    public function drawPixel($x, $y, $colorName)
    {
        imagesetpixel($this->image, $x, $y, $this->allocateColor($colorName));
    }


    /**
     * Prints some text string at specified X/Y coords with default font
     * 
     * @param int $x
     * @param int $y
     * @param string $text
     * @param string $colorName
     * @param int $size=1
     * @param bool $vertical
     * 
     * @return void
     */
    public function drawString($x, $y, $text, $colorName, $size = 1, $vertical = false)
    {
        if (!empty($text)) {
            if ($vertical) {
                imagestringup($this->image, $size, $x, $y, $text, $this->allocateColor($colorName));
            } else {
                imagestring($this->image, $size, $x, $y, $text, $this->allocateColor($colorName));
            }
        }
    }

    /**
     * Write text to the image using TrueType fonts
     * 
     * @param int $x
     * @param int $y
     * @param string $text
     * @param string $colorName
     * 
     * @return void
     */
    public function drawText($x, $y, $text, $colorName)
    {
        if (!empty($text)) {
            imagettftext($this->image, $this->fontSize, 0, $x, $y, $this->allocateColor($colorName), $this->font, $text);
        }
    }

    /**
     * Set the thickness for line drawing in px
     * 
     * @param int $lineWidth 
     * 
     * @return void
     */
    public function setLineWidth($lineWidth)
    {
        $this->lineWidth=$lineWidth;
        imagesetthickness($this->image, $this->lineWidth);
    }

    /**
     * Draws filled rectangle
     * 
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param string $colorName
     * 
     * @return void
     */
    public function drawRectangle($x1, $y1, $x2, $y2, $colorName)
    {
        if (isset($this->colorPalette[$colorName])) {
            $colorData = $this->colorPalette[$colorName];
            if (isset($this->colorsAllocated[$colorName])) {
                $drawingColor = $this->colorsAllocated[$colorName];
            } else {
                $drawingColor = imagecolorallocate($this->image, $colorData['r'], $colorData['g'], $colorData['b']);
                $this->colorsAllocated[$colorName] = $drawingColor;
            }

            imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $drawingColor);
        } else {
            throw new Exception('EX_COLOR_NOT_EXISTS:' . $colorName);
        }
    }

    /**
     * Draws a line
     * 
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param string $colorName
     * 
     * @return void
     */
    public function drawLine($x1, $y1, $x2, $y2, $colorName)
    {
        imageline($this->image, $x1, $y1, $x2, $y2, $this->allocateColor($colorName));
    }


    /**
     * Puts preloaded watermark on base image
     * 
     * @param int $stretch=true
     * @param int $x=0
     * @param int $y=0
     * 
     * @return void
     */
    public function drawWatermark($stretch = true, $x = 0, $y = 0)
    {
        imagealphablending($this->watermark, false);
        $watermarkWidth = imagesx($this->watermark);
        $watermarkHeight = imagesy($this->watermark);
        if ($stretch) {
            imagecopyresampled($this->image, $this->watermark, $x, $y, 0, 0, $this->imageWidth, $this->imageHeight, $watermarkWidth, $watermarkHeight);
        } else {
            imagecopy($this->image, $this->watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
        }
    }


    /**
     * Applies pixelation filter
     * 
     * @param int $blockSize
     * @param bool $smooth=true
     * 
     * @return void
     */
    public function pixelate($blockSize, $smooth = true)
    {
        imagefilter($this->image, IMG_FILTER_PIXELATE, $blockSize, $smooth);
    }
}
