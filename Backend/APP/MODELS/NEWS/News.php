<?php

namespace MODELS\NEWS;

#region REQUIRE
require_once(__DIR__ . "/../GEOGRAPHY/City.php");
require_once(__DIR__ . "/../ACCOUNT/Writer.php");
require_once(__DIR__ . "/../ACCOUNT/Media.php");
#endregion

#region USE
use MODELS\GEOGRAPHY\City;
use MODELS\ACCOUNT\Writer;
use MODELS\ACCOUNT\Media;
use Exception;
use Generator;

#endregion

class News
{
    #region FIELDS
    private int $id;
    private string $title;
    private string $slug;
    private string $content;
    private array $images = [];
    private Writer $author;
    private Media $media;
    private City $city;
    private string $category;
    private int $view_count = 0;
    private int $like_count = 0;
    private array $tags = [];
    private float $rating = 0.0;
    private string $created_at;
    private string $updated_at;

    #endregion

    #region CONSTRUCTOR
    public function __construct()
    {
    }
    #endregion

    #region GETTERS
    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getAuthor(): Writer
    {
        return $this->author;
    }

    public function getCIty(): City
    {
        return $this->city;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getViewCount(): int
    {
        return $this->view_count;
    }

    public function getLikeCount(): int
    {
        return $this->like_count;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    #region SETTERS
    public function setId(int $id): self
    {
        if ($id <= 0) {
            throw new Exception("Invalid news ID");
        }
        $this->id = $id;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $title = trim($title);
        if ($title === '') {
            throw new Exception("Title cannot be empty");
        }
        $this->title = $title;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new Exception("Invalid slug format");
        }
        $this->slug = $slug;
        return $this;
    }

    public function setContent(string $content): self
    {
        if (trim($content) === '') {
            throw new Exception("Content cannot be empty");
        }
        $this->content = $content;
        return $this;
    }

    public function setImages(array $images): self
    {
        $this->images = $images;
        return $this;
    }

    public function addImage(string $image): self
    {
        if (trim($image) === '') {
            throw new Exception("Image path cannot be empty");
        }
        $this->images[] = $image;
        return $this;
    }

    public function setAuthor(Writer $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function setCategory(string $category): self
    {
        if (trim($category) === '') {
            throw new Exception("News category cannot be empty");
        }
        $this->category = $category;
        return $this;
    }

    public function setViewCount(int $view_count): self
    {
        if ($view_count < 0) {
            throw new Exception("News View count cannot be negative");
        }
        $this->view_count = $view_count;
        return $this;
    }

    public function setLikeCount(int $like_count): self
    {
        if ($like_count < 0) {
            throw new Exception("News Like count cannot be negative");
        }
        $this->like_count = $like_count;
        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = array_values(array_unique(array_map('trim', $tags)));
        return $this;
    }

    public function addTag(string $tag): self
    {
        $tag = trim($tag);
        if ($tag === '') {
            throw new Exception("News Tag cannot be empty");
        }

        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function setRating(float $rating): self
    {
        if ($rating < 0 || $rating > 5) {
            throw new Exception("News Rating must be between 0 and 5");
        }
        $this->rating = $rating;
        return $this;
    }

    public function setMedia(Media $media): self
    {
        $this->media = $media;
        return $this;
    }

    public function setCreatedAt(string $created_at): self
    {

        if (!preg_match(REGEX_DATE_TIME, $created_at)) {
            throw new Exception("News created_at has invalid datetime format. Expected YYYY-MM-DD HH:mm:SS");
        }

        $this->created_at = $created_at;
        return $this;
    }
    public function setUpdatedAt(string $updated_at): self
    {

        if (!preg_match(REGEX_DATE_TIME, $updated_at)) {
            throw new Exception("News created_at has invalid datetime format. Expected YYYY-MM-DD HH:mm:SS");
        }

        $this->updated_at = $updated_at;
        return $this;
    }
    #endregion

    #region UTILITIES
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'images' => $this->images,
            'category' => $this->category,
            'view_count' => $this->view_count,
            'like_count' => $this->like_count,
            'rating' => $this->rating,
            'tags' => $this->tags,
            'author' => $this->author->toArray(),
            'city' => $this->city->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    #endregion
}
