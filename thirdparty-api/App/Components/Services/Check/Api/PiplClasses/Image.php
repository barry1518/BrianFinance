<?php
namespace Check\Api\PiplClasses;
/**
 * Class Image
 * @package Check\Api\PiplClasses
 */
class Image extends Field{

    protected $children = ['url', 'thumbnail_token'];

    /**
     * Image constructor.
     * @param array $params
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `url` should be a string.
        // `thumbnail_token` is a string used to create the URL for Pipl's thumbnail service.
        if (!empty($url)){
            $this->url = $url;
        }
        if (!empty($thumbnail_token)){
            $this->thumbnail_token = $thumbnail_token;
        }
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the image URL is a valid URL.
     */
    public function is_valid_url():bool {

        return (!empty($this->url) && Utils::piplapi_is_valid_url($this->url));
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $zoom_face
     * @param bool $favicon
     * @param bool $use_https
     * @return null|string
     */
    public function get_thumbnail_url(int $width=100, int $height=100,bool $zoom_face=true, bool $favicon=true,
        bool $use_https=false):?string {

        if(!empty($this->thumbnail_token)){
            return self::generate_redundant_thumbnail_url($this);
        }
        return NULL;
    }

    /**
     * @param Image $first_image
     * @param Image|NULL $second_image
     * @param int $width
     * @param int $height
     * @param bool $zoom_face
     * @param bool $favicon
     * @param bool $use_https
     * @return string
     */
    public static function generate_redundant_thumbnail_url(Image $first_image,Image $second_image=NULL,int $width=100,
         int $height=100, bool $zoom_face=true, bool $favicon=true, bool $use_https=false):string {

        if (empty($first_image) && empty($second_image))
            throw new \InvalidArgumentException('Please provide at least one image');


        if ((!empty($first_image) && !($first_image instanceof Image)) ||
            (!empty($second_image) && !($second_image instanceof Image))){
            throw new \InvalidArgumentException('Please provide Image Object');
        }

        $images = [];

        if (!empty($first_image->thumbnail_token))
            $images[] = $first_image->thumbnail_token;

        if (!empty($second_image->thumbnail_token))
            $images[] = $second_image->thumbnail_token;

        if (empty($images))
            throw new \InvalidArgumentException("You can only generate thumbnail URLs for image objects with a thumbnail token.");

        if (sizeof($images) == 1)
            $tokens = $images[0];
        else {
            foreach ($images as $key=>$token) {
                $images[$key] = preg_replace("/&dsid=\d+/i","", $token);
            }
            $tokens = join(",", array_values($images));
        }

        $prefix = $use_https ? "https" : "http";
        $params = ["width" => $width, "height" => $height, "zoom_face" => $zoom_face, "favicon" => $favicon];
        $url = $prefix . "://thumb.pipl.com/image?tokens=" . $tokens . "&" . http_build_query($params);
        return $url;
    }

    /**
     * @return string
     */
    public function __toString():string {

        return $this->url;
    }
}
