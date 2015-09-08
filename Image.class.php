<?php

/**
 * Created by User: fegrace
 * Date: 2015/9/7 10:27
 * 图像处理类，文字水印，Logo水印，验证码，缩略图
 */

//Image::$text_alpha = 0;
//Image::$is_save = true;
//$obj = new Image('3.jpg');
//$obj->setPosition('left','top');
//$obj->fontMark();
//$obj->setLogoPosition('center','top');
//$obj->logoMark();
//Image::$thumb_width = 300;
//Image::$thumb_height = 200;
//$obj->thumb();

//Image::verify();
Image::verify(6, 150, 30);


class Image
{
    public $src;            //需要处理的图片文件位置
    public $src_info = [];  //需要处理的图片信息
    public $src_img;        //需要处理的图片创建资源画布

    public static $is_show = true;      //是否显示
    public static $is_save = false;     //是否保存
    public static $quality = 80;    //图片质量
    public static $save_path = './water/';

    public static $text = 'qwphp';
    public static $font_size = 20;
    public static $font_color = [
        'r' => 255,
        'g' => 255,
        'b' => 255,
    ];
    public static $text_alpha = 50;         //文字透明度
    public static $text_angle = 10;         //文字角度
    public static $text_x = 'left';         //文本X轴
    public static $text_y = 'top';          //文本Y轴
    public static $text_x_offset = 20;      //文本x偏移 设置位置时候用
    public static $text_y_offset = 20;      //文本y偏移
    public static $text_box = [];           //文本盒子信息
    public static $font = 'msyh.ttf';

    public static $logo = 'logo.png';       //Logo文件位置
    public static $logo_info = [];          //Logo信息
    public static $logo_img;                //Logo创建资源画布
    public static $logo_x = 'left';         //文本X轴
    public static $logo_y = 'top';          //文本Y轴
    public static $logo_x_offset = 20;      //文本x偏移 设置位置时候用
    public static $logo_y_offset = 20;      //文本y偏移
    public static $logo_alpha = 50;         //Logo透明度

    public static $thumb_width = 200;
    public static $thumb_height = 150;

    public static $verify_len = 4;
    public static $verify_width = 120;
    public static $verify_height = 35;
    public static $verify_seed = '0123456789qwertyuiopasdfghjklzxcvbnm';


    public function __construct($src)
    {
        $this->src = $src;
    }

    /*创建需要处理图像资源*/
    public function create()
    {
        $this->src_info = static::getImageInfo($this->src);
        $this->src_img = $this->src_info['func_create']($this->src);
    }

    /*创建Logo图像资源*/
    public static function logoCreate()
    {
        static::$logo_info = static::getImageInfo(static::$logo);
        $func_create = static::$logo_info['func_create'];
        static::$logo_img = $func_create(static::$logo);
    }

    /*文字水印*/
    public function fontMark()
    {
        if ($this->fileCheck('font')) {
            $this->create();
            extract(static::$font_color);
            $image_color = imagecolorallocatealpha($this->src_img, $r, $g, $b, static::$text_alpha);

            if (!is_numeric(static::$text_x)) {
                $this->setFontPosition(static::$text_x, static::$text_y);
            }
            imagettftext($this->src_img, static::$font_size, static::$text_angle, static::$text_x, static::$text_y, $image_color, static::$font, static::$text);

            $this->output(__FUNCTION__);
        }
    }

    /*logo 水印*/
    public function logoMark()
    {
        if ($this->fileCheck('logo')) {
            if (!is_resource(static::$logo_img)) {
                static::logoCreate();
            }
            $this->create();
            if (!is_numeric(static::$logo_x)) {
                $this->setLogoPosition(static::$logo_x, static::$logo_y);
            }
            if (static::$logo_info['type'] == 'png') {
                imagecopy($this->src_img, static::$logo_img, static::$logo_x, static::$logo_y, 0, 0, static::$logo_info['width'], static::$logo_info['height']);
            } else {
                imagecopymerge($this->src_img, static::$logo_img, static::$logo_x, static::$logo_y, 0, 0, static::$logo_info['width'], static::$logo_info['height'], static::$logo_alpha);
            }
            $this->output(__FUNCTION__);
        }

    }

    /*缩略图*/
    public function thumb()
    {
        $this->create();

        /*创建一个真彩色画布*/
        $thumb_img = imagecreatetruecolor(static::$thumb_width, static::$thumb_height);

        imagecopyresampled($thumb_img, $this->src_img, 0, 0, 0, 0, static::$thumb_width, static::$thumb_height, $this->src_info['width'], $this->src_info['height']);
        $this->src_img = $thumb_img;    //输出的时候用到

        $this->output(__FUNCTION__);
    }

    /*验证码*/
    public static function verify($len = null, $width=null, $height =null)
    {
        !is_null($len) && (static::$verify_len = $len);
        !is_null($width) && (static::$verify_width = $width);
        !is_null($height) && (static::$verify_height = $height);

        //声明头部
        header('Content-Type: image/jpeg');
        //创建画布
        $img = imagecreatetruecolor(static::$verify_width, static::$verify_height);
        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);

        //画线方法
        $line = function($img)
        {
            for ($i = 0; $i < 10; $i++) {
                $color = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                imageline($img, mt_rand(0, static::$verify_width), mt_rand(0, static::$verify_height), mt_rand(0, static::$verify_width), mt_rand(0, static::$verify_height), $color);
            }
        };

        //画点方法
        $point = function ($img)
        {
            for ($i = 0; $i < 50; $i++) {
                $color = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                imagesetpixel($img, mt_rand(0, static::$verify_width), mt_rand(0, static::$verify_height), $color);
            }
        };

        //画文字方法
        $text = function($img)
        {
            $seed_num = strlen(static::$verify_seed) - 1;
            $step = static::$verify_width / static::$verify_len;
            $y = static::$verify_height / 2 + 8;
            for ($i = 0; $i < static::$verify_len; $i++) {
                $color = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                imagettftext($img, static::$font_size, mt_rand(-15, 15), $i * $step + 10, $y, $color, static::$font, static::$verify_seed[mt_rand(0, $seed_num)]);
            }
        };

        //画线，点，文字
        $line($img);
        $point($img);
        $text($img);

        //输出图像
        imagejpeg($img);
        //销毁图像
        imagedestroy($img);

    }


    //字体文件检查
    public function fileCheck($type = 'font')
    {
        try {
            switch ($type) {
                case 'font':
                    $is_file = is_file(static::$font);
                    $error_msg = '字体文件不存在';
                    break;
                case 'logo':
                    $is_file = is_file(static::$logo);
                    $error_msg = 'Logo文件不存在';
                    break;
                case 'src':
                    $is_file = is_file($this->src);
                    $error_msg = '需要处理文件不存在';
                    break;
                default:
                    $is_file = false;
                    $error_msg = '文件件不存在';
                    break;
            }

            if ($is_file) {
                return true;
            } else {
                throw new Exception($error_msg);
                return false;
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /*设置Logo位置*/
    public function setLogoPosition($x, $y)
    {
        if (!is_resource($this->src_img)) {
            $this->create();
        }
        if (!is_resource(static::$logo_img)) {
            static::logoCreate();
        }
        switch ($x) {
            case 'left':
                static::$logo_x = static::$logo_x_offset;
                break;
            case 'center':
                static::$logo_x = ($this->src_info['width'] - static::$logo_info['width']) / 2 - static::$logo_x_offset;
                break;
            case 'right':
                static::$logo_x = ($this->src_info['width'] - static::$logo_info['width']) - static::$logo_x_offset;
                break;
            default:
                static::$logo_x = $x;
                break;
        }

        switch ($y) {
            case 'top':
                static::$logo_y = static::$logo_y_offset;
                break;
            case 'middle':
                static::$logo_y = ($this->src_info['height'] - static::$logo_info['height']) / 2 - static::$logo_y_offset;
                break;
            case 'bottom':
                static::$logo_y = ($this->src_info['height'] - static::$logo_info['height']) - static::$logo_y_offset;
                break;
            default:
                static::$logo_y = $y;
                break;
        }
    }

    /*设置文字位置*/
    public function setFontPosition($x, $y)
    {
        if (!is_resource($this->src_img)) {
            $this->create();
        }
        if (empty(static::$text_box)) {
            static::$text_box = static::calculateTextBox(static::$text, static::$font, static::$font_size, static::$text_angle);
        }

        switch ($x) {
            case 'left':
                static::$text_x = static::$text_x_offset + static::$text_box['left'];
                break;
            case 'center':
                static::$text_x = ($this->src_info['width'] - static::$text_box['width']) / 2 - static::$text_x_offset;
                break;
            case 'right':
                static::$text_x = ($this->src_info['width'] - static::$text_box['width']) - static::$text_x_offset;
                break;
            default:
                static::$text_x = $x;
                break;
        }

        switch ($y) {
            case 'top':
                static::$text_y = static::$text_y_offset + static::$text_box['top'];
                break;
            case 'middle':
                static::$text_y = ($this->src_info['height'] - static::$text_box['height']) / 2 + static::$text_y_offset;
                break;
            case 'bottom':
                static::$text_y = ($this->src_info['height'] - static::$text_box['height']) + static::$text_y_offset;
                break;
            default:
                static::$text_y = $y;
                break;
        }
    }

    /*展示图像*/
    public function show()
    {
        header('Content-Type: ' . $this->src_info['mime']);
        $this->src_info['func_output']($this->src_img);
    }

    /*保存图像*/
    public function save($funName)
    {
        is_dir(static::$save_path) || mkdir(static::$save_path, 0777, true);

        $filename = static::$save_path . '/' . $funName . '_' . basename($this->src);
        $this->src_info['func_output']($this->src_img, $filename, static::$quality);
    }

    /*输出处理*/
    public function output($funName)
    {
        static::$is_show && $this->show();
        static::$is_save && $this->save($funName);
    }

    /*销毁资源*/
    public function __destruct()
    {
        is_resource($this->src_img) && imagedestroy($this->src_img);
    }

    /*获取图像信息*/
    public static function getImageInfo($file)
    {
        try {
            if (is_file($file)) {
                $info = getimagesize($file);
                $data = [];
                $data['width'] = $info[0];
                $data['height'] = $info[1];
                $data['mime'] = $info['mime'];
                $data['type'] = image_type_to_extension($info[2], false);
                $data['func_create'] = 'imagecreatefrom' . $data['type'];
                $data['func_output'] = 'image' . $data['type'];
                return $data;
            } else {
                throw new Exception("文件不存在");
                return false;
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /*计算文本盒子数据*/
    public static function calculateTextBox($text, $fontFile, $fontSize, $fontAngle)
    {
        $rect = imagettfbbox($fontSize, $fontAngle, $fontFile, $text);
        $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
        $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

        return array(
            "left" => abs($minX) - 1,
            "top" => abs($minY) - 1,
            "width" => $maxX - $minX,
            "height" => $maxY - $minY,
            "box" => $rect
        );
    }
}
