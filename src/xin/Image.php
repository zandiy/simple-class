<?php

namespace misterxin;

class Image
{
    private $info = [];
    private $width; //原始图片宽度
    private $height; //图片原始高度
    private $mime; //图片mime类型 自 PHP 4.3.0 起，getimagesize() 还会返回额外的参数 mime，符合该图像的 MIME 类型
    private $image; //图像资源
    private $format; //图像格式
    private $ratio; //角度
    private $font; //角度
    // 1、打开图片 读取到内存中
    public function __construct($src)
    {
        $this->info = getimagesize($src);
        $this->width = $this->info['0'];
        $this->height = $this->info['1'];
        $this->mime = $this->info['mime'];
        $func = str_replace('/', 'createfrom', $this->mime);
        $filearray = explode(".", $src);
        //end() 将 array 的内部指针移动到最后一个单元并返回其值。  mixed end ( array &$array )
        $this->format = strtolower(end($filearray)); //strtolower(end(explode('.', $src))) Only variables should be passed by reference不能连在一起写
        $this->image = $func($src); //返回一图像标识符，代表了从给定的文件名取得的图像。
        $this->ratio = rad2deg(atan2($this->height, $this->width));
        $this->font = get_path('static') . 'font/auxin.otf'; //注意字体路径要写对，否则显示不了图片

    }
    /**
     * [thumb description]
     * 操作图片 压缩
     * @DateTime 2019-07-27T16:10:38+0800
     * @param    [type]                   $width        [图片宽度或者最大宽度]
     * @param    [type]                   $height       [图片高度或者最大高度]
     * @param    boolean                  $flag         [是否等比例缩放]
     * @param    integer                  $scale        [缩放比例为0时不缩放按宽高比]
     * @return   [type]                                 [description]
     */
    public function thumb($width = null, $height = null, $flag = true, $scale = 0)
    {
        $ratio = round($this->width / $this->height, 2); //宽高比 比值越大图片越扁
        $dstratio = $width && $height ? round($width / $height, 2) : 1;
        // 根据不同的情况计算缩放图的宽高
        if ($scale) {
            $dst_width = floor($this->width * $scale);
            $dst_height = floor($this->height * $scale);
        }
        if ($width && $height && !$flag) {
            $dst_width = $width;
            $dst_height = $height;
        }
        if (!$scale && $flag) {
            if ($ratio > $dstratio) {
                $dst_width = $width;
                $dst_height = floor($width / $ratio);
            } elseif ($ratio < $dstratio) {
                $dst_width = floor($height * $ratio);
                $dst_height = $height;
            } else {
                $dst_width = $width;
                $dst_height = $height;
            }
        }
        $dst_image = imagecreatetruecolor($dst_width, $dst_height);
        imagealphablending($dst_image, false); //关闭混合模式，以便透明颜色能覆盖原画板
        imagesavealpha($dst_image, true); //设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息（与单一透明色相反）。 要使用本函数，必须将 alphablending 清位（imagealphablending($im, false)       
        imagecopyresampled($dst_image, $this->image, 0, 0, 0, 0, $dst_width, $dst_height, $this->width, $this->height);
        imagedestroy($this->image);
        $this->image = $dst_image;
    }
    /**
     * [addTextwatermark description]
     * 给图片加文字水印
     * @DateTime 2019-07-28T17:27:42+0800
     * @param    [mixed]                   $text         [要加的文字多行的话要存成数组]   
     * @param    integer                  $fontsize         [description]
     * @param    integer                  $angle        [description]
     * @param    integer                  $point        [description]
     */

    public function textmark($text, $fontsize = 20, $color = [255, 255, 255, 50], $angle = 0, $point = 9)
    {
        $textcolor = imagecolorallocatealpha($this->image, $color[0], $color[1], $color[2], $color[3]);
        $angle = $angle == false ? $this->ratio : $angle;
        $textlength = is_array($text) && count($text) > 1 ? count($text) : 1; //多行文字
        $textSize = imagettfbbox($fontsize, $angle, $this->font, $text);
        $textWidth = $textSize[2] - $textSize[1]; //文字的最大宽度
        $textHeight = $textSize[1] - $textSize[7]; //文字的高度
        $lineHeight = $textlength == 1 ? $textHeight : $textHeight + 3; //文字的行高
        //是否可以添加文字水印 只有图片的可以容纳文字水印时才添加
        if ($textWidth + 40 > $this->width || $lineHeight * $textlength + 40 > $this->height) {
            return false; //图片太小了，无法添加文字水印
        }
        $pointxy = $this->markLocation(array('width' => $this->width, 'height' => $this->height), array('width' => $textWidth, 'height' => $lineHeight), $point, $angle);
        imagettftext($this->image, $fontsize, $angle, $pointxy['x'], $pointxy['y'] + $lineHeight * $textlength, $textcolor, $this->font, $text);
    }
    /**
     * [addPicwatermark description]
     * 添加图片水印
     * @DateTime 2019-07-30T09:40:53+0800
     * @param    [string]                   $markimg [水印图片路径]
     * @param    integer                  $point   [水印图所处位置默认为左上角]
     */
    public function picmark($markimg, $point = 1)
    {
        $info = getimagesize($markimg);
        $markWidth = $info[0];
        $markHight = $info[1];
        $func = str_replace('/', 'createfrom', $info['mime']);
        $filearray = explode(".", $markimg);
        $format = strtolower(end($filearray));
        $mark_image = $func($markimg); //返回一图像标识符，代表了从给定的文件名取得的图像。
        imagealphablending($mark_image, false); //关闭混合模式，以便透明颜色能覆盖原画板
        imagesavealpha($mark_image, true);
        $pointxy = $this->markLocation(array('width' => $this->width, 'height' => $this->height), array('width' => $markWidth, 'height' => $markWidth), $point, 0);
        imagecopy($this->image, $mark_image, $pointxy['x'], $pointxy['y'], 0, 0, $markWidth, $markHight);
        imagedestroy($mark_image);
    }
    /**
     * [createCircleimg description]
     * 生成圆角png图像
     * @DateTime 2019-07-30T11:21:33+0800
     * @return   [type]                   [description]
     */
    public function round()
    {
        $diameter = $this->width > $this->height ? floor($this->height / 2) * 2 : floor($this->width / 2) * 2;
        $Circleimg = imagecreatetruecolor($diameter, $diameter);
        // imagesavealpha($Circleimg, true);
        $bg = imagecolorallocatealpha($Circleimg, 255, 255, 255, 127);
        $border = []; //此处没有完善，需要再改进
        if (is_array($border) && count($border) == 3) {
            $border_color = imagecolorallocate($Circleimg, 0, 0, 0);
        } else {
            //$border_color= imagecolorallocatealpha($Circleimg, 255, 255, 255, 127);
            $border_color = imagecolorallocate($Circleimg, 0, 0, 0);
        }

        imagealphablending($Circleimg, false);
        imagesavealpha($Circleimg, true);
        $r = floor($diameter / 2);
        if ($this->width > $this->height) {
            $plusx = round(($this->width - $this->height) / 2);
            $plusy = 0;
        } else {
            $plusy = round(($this->height - $this->width) / 2);
            $plusx = 0;
        }
        for ($x = 0; $x < $diameter; $x++) {
            for ($y = 0; $y < $diameter; $y++) {
                $rgbColor = imagecolorat($this->image, $x + $plusx, $y + $plusy);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($Circleimg, $x, $y, $rgbColor);
                } else {
                    imagesetpixel($Circleimg, $x, $y, $bg);
                }
            }
        }
        imagedestroy($this->image);
        $this->image = $Circleimg;
        $this->mime = 'image/png';
        $this->format = 'png';
    }

    /**
     * [markLocation description]
     * 位置函数
     * @DateTime 2019-07-28T17:22:17+0800
     * @param    [array]                   $outerbox [外层容器宽高] array('width'=>'','height'=>'')
     * @param    [array]                   $innerbox [内层容器宽高]array('width'=>'','height'=>'')
     * @param    [int]                   $point    [位置]$point 1、左上角 2，上居中 3，右上角 4、右居中 5，右下角 6，下居中 7，左下角 8，左居中 9、居中
     * @param    [int]                   $angle    [偏移角度]
     * @param    integer                  $padding  [内距]
     * @return   [mixed]                             [成功返回位移数组]
     */
    public function markLocation($outerbox, $innerbox, $point, $angle, $padding = 20)
    {

        if (!is_array($outerbox) || !is_array($innerbox)) return false;
        switch ($point) {
            case 1: //左上角
                $pointLeft = $padding;
                $pointTop = $padding;
                break;
            case 2:
                $pointLeft = ($outerbox['width'] - $innerbox['width']) / 2;
                $pointTop = $padding;
                break;
            case 3:
                $pointLeft = $outerbox['width'] - $innerbox['width'] - $padding;
                $pointTop = $padding;
                break;
            case 4:
                $pointLeft = $outerbox['width'] - $innerbox['width'] - $padding;
                $pointTop = ($outerbox['height'] - $innerbox['height']) / 2;
                break;
            case 5:
                $pointLeft = $outerbox['width'] - $innerbox['width'] - $padding;
                $pointTop = $outerbox['height'] - $innerbox['height'] - $padding;
                break;
            case 6:
                $pointLeft = ($outerbox['width'] - $innerbox['width']) / 2;
                $pointTop = $outerbox['height'] - $innerbox['height'] - $padding;
                break;
            case 7: //左上角
                $pointLeft = $padding;
                $pointTop = $outerbox['height'] - $innerbox['height'] - $padding;
                break;
            case 8:
                $pointLeft = $padding;
                $pointTop = ($outerbox['height'] - $innerbox['height']) / 2;
                break;
            case 9:
                $pointLeft = ($outerbox['width'] - $innerbox['width']) / 2;
                $pointTop = ($outerbox['height'] - $innerbox['height']) / 2;
                break;
            default;
        }
        if ($angle != 0) {
            if ($angle < 90) {
                //画一下图 根据三角关系得到偏移量
                $diffTop = ceil(sin($angle * M_PI / 180) * $innerbox['width']);
                $diffLeft = ceil(sin($angle * M_PI / 180) * $innerbox['height']);
                if (in_array($point, array(1, 2, 3))) { // 上部 top 值增加
                    $pointTop += ($diffTop - $diffLeft / 2);
                } elseif (in_array($point, array(4, 8, 9))) { // 中部 top 值根据图片总高判断
                    if ($innerbox['width'] + $innerbox['height'] / 2 > ceil($outerbox['height'] / 2)) {
                        $pointTop += ceil(($innerbox['width'] - $outerbox['height'] / 2) / 2);
                        $diagonal = sqrt(pow($this->width, 2) + pow($this->height, 2)) / 2;
                        $pointLeft = ($outerbox['width'] - ceil(cos($angle * M_PI / 180) * $innerbox['width'])) / 2;
                    }
                }
            } elseif ($angle > 270) {
                $diffTop = ceil(sin((360 - $angle) * M_PI / 180) * $innerbox['width']);

                if (in_array($point, array(1, 2, 3))) { // 上部 top 值增加
                    $pointTop -= $diffTop;
                } elseif (in_array($point, array(4, 8, 9))) { // 中部 top 值根据图片总高判断
                    if ($innerbox['width'] > ceil($outerbox['height'] / 2)) {
                        $pointTop = ceil(($outerbox['height'] - $diffTop) / 2);
                    }
                }
            }
        }
        return array('x' => intval($pointLeft), 'y' => intval($pointTop));
    }
    /**
     * [outputInBrower description]
     * 把图片在浏览器输出
     * @DateTime 2019-07-27T16:48:33+0800
     * @return   [type]                   [description]
     */
    public function output()
    {
        header('content-type:' . $this->mime);
        $outfunc = str_replace('/', '', $this->mime);
        $outfunc($this->image);
    }

    /**
     * [outputAsFile description]
     * 图片保存为文件
     * @DateTime 2019-07-27T17:00:26+0800
     * @param    [string]                   $destionation [图片保存路径]
     * @return   [type]                                 [description]
     */
    public function save($destionation = null)
    {
        $outfunc = str_replace('/', '', $this->mime);
        $randname = md5(time());
        $destionation = $destionation ?: get_path('static') . 'upload/' . $randname . '.' . $this->format;
        if ($destionation && !file_exists(dirname($destionation))) {
            mkdir(dirname($destionation), 0777, true);
        }
        //$dstFilename= $destionation==null ? $destionation : $destionation.'/'.$randname.'.'.$this->format;
        //$dstFilename= $destionation.'/'.$randname.'.'.$this->format;


        return $outfunc($this->image, $destionation);
    }
    public function __destruct()
    {
        imagedestroy($this->image);
    }
}
