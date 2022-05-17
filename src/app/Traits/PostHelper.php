<?php

namespace VCComponent\Laravel\Post\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait PostHelper
{
    /**
     * Get the user record associated with the post.
     */
    public function author()
    {
        if (config('auth.providers.users.model')) {
            return $this->belongsTo(config('auth.providers.users.model'));
        } else {
            return $this->belongsTo(\VCComponent\Laravel\User\Entities\User::class);
        }
    }

    /**
     * get post raw title
     * 
     * @return string 
     */
    public function getRawTitle()
    {
        return $this->title;
    }

    /**
     * get post formated title with html tag
     * 
     * @param Collection|array $attributes
     * @return string
     */
    public function getFormatedTitle($attributes = [])
    {
        $attributes_string = $this->formatHtmlAttributes($attributes);

        return "<h1" . $attributes_string . ">" . $this->title . "</h1>";
    }

    /**
     * get post created at
     * 
     * @param string $format
     * @param string $tz
     * @param string $lang
     * @return string 
     */
    public function getRawCreatedAt($format = null, $tz = null, $lang = 'en')
    {
        $created_at = $this->published_date ? $this->published_date : $this->created_at;

        if ($format) {
            $tz = $tz ? $tz : config('app.timezone');
            return Carbon::createFromTimeString($created_at)->setTimezone($tz)->locale($lang)->isoFormat($format);
        } else {
            return $created_at;
        }
    }

    /**
     * get post thumbnail
     * 
     * @return string 
     */
    public function getRawThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * get post formated thumbnail
     * 
     * @return string 
     */
    public function getFormatedThumbnail($lazyload_src_attribute = "src", $alt = null, $attributes = [])
    {
        $attributes_string = $this->formatHtmlAttributes($attributes);

        if ($alt) {
            return '<img ' . $lazyload_src_attribute . '="' . $this->thumbnail . '"' 
                . $attributes_string . ' alt="' . $alt . '"/>';
        } else {
            return '<img ' . $lazyload_src_attribute . '="' . $this->thumbnail . '"' 
                . $attributes_string . ' alt="' . $this->title . '"/>';
        }
    }

    /**
     * get post link
     * 
     * @return string 
     */
    public function getRawLink()
    {
        return "/" . $this->type . "/" . $this->slug;
    }

    /**
     * get post formated link
     * 
     * @param string $display_text
     * @param Collection|array $attributes
     * @return string 
     */
    public function getFormatedLink($display_text = null, $attributes = [])
    {
        $attributes_string = $this->formatHtmlAttributes($attributes);

        if ($display_text) {
            return '<a href="' . $this->getRawLink() . '"' . $attributes_string . ' hrefLang="' . app()->getLocale() . '">' . $display_text . '</a>';
        } else {
            return '<a href="' . $this->getRawLink() . '"' . $attributes_string . ' hrefLang="' . app()->getLocale() . '">' . $this->title . '</a>';
        }
    }

    /**
     * get post formated link
     * 
     * @param string $display_text
     * @param Collection|array $attributes
     * @return string|null 
     */
    public function getRawAuthorName()
    {
        if ($this->author) {
            return $this->author->first_name . " " . $this->author->last_name;
        } else {
            return null;
        }
    }

    /**
     * get post raw description
     */
    public function getRawDescription()
    {
        return $this->description;
    }

    /**
     * get post raw content
     */
    public function getRawContent()
    {
        return $this->content;
    }

    /**
     * format attribute to string to be able to merge with html string
     * 
     * @param Collection|array $attributes
     * @return string
     */
    private function formatHtmlAttributes($attributes)
    {
        if (!$attributes instanceof Collection) $attributes = collect($attributes);

        $attributes_string = '';
        $attributes->each(function ($value, $attribute) use ($attributes_string) {
            $attributes_string .= ' "' . $attribute . '"="' . $value . '"';
        });

        return $attributes_string;
    }
}
