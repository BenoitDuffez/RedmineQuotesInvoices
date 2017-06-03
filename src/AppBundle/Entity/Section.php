<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Section
 *
 * @ORM\Table(name="section")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SectionRepository")
 */
class Section
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer")
     */
    private $rate;

    /**
     * @var int
     *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quote",inversedBy="sections")
	 * @ORM\JoinColumn(name="quote_id", referencedColumnName="id")
     */
    private $quote;

	/**
	 * @var ArrayCollection
	 * 
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\Item", mappedBy="section", cascade={"all"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	private $items;

	public function __construct() {
		$this->items = new ArrayCollection();
	}

	/**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Section
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Section
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set rate
     *
     * @param integer $rate
     *
     * @return Section
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set quote
     *
     * @param Quote $quote
     *
     * @return Section
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return int
     */
    public function getQuote()
    {
        return $this->quote;
    }

	/**
	 * @return mixed
	 */
	public function getItems() {
		return $this->items;
	}

	public function addItem(Item $item)
	{
		$item->setSection($this);
		$this->items->add($item);
		return $this;
	}

	public function removeItem(Item $item)
	{
		$this->items->removeElement($item);
		return $this;
	}
}

