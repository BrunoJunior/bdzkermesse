<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 01/08/2018
 * Time: 11:33
 */

namespace App\Helper;


class BreadcrumbLink
{
    /**
     * @var ?string
     */
    private $href;

    /**
     * @var ?string
     */
    private $icon;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $name;

    /**
     * BreadcrumbLink constructor.
     * @param string $name
     * @param null|string $icon
     * @param null|string $href
     * @param bool $active
     */
    public function __construct(string $name, ?string $icon = null, ?string $href = null)
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->href = $href;
        $this->active = false;
    }

    /**
     * @param string $name
     * @param null|string $icon
     * @param null|string $href
     * @return static
     */
    public static function getInstance(string $name, ?string $icon = null, ?string $href = null): self
    {
        return new static($name, $icon, $href);
    }

    /**
     * @return string|null
     */
    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param bool $active
     * @return static
     */
    public function setActive(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}