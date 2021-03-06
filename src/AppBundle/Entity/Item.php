<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item
 *
 * @ORM\Table(name="item")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 */
class Item {
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="position", type="integer")
	 */
	private $position;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="hours", type="decimal", precision=10, scale=2)
	 */
	private $hours;

	/**
	 * @var Section
	 *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Section",inversedBy="items")
	 * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
	 */
	private $section;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text")
	 */
	private $description;

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get position
	 *
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Set position
	 *
	 * @param integer $position
	 *
	 * @return Item
	 */
	public function setPosition($position) {
		$this->position = $position;

		return $this;
	}

	/**
	 * Get hours
	 *
	 * @return string
	 */
	public function getHours() {
		return $this->hours;
	}

	/**
	 * Set hours
	 *
	 * @param string $hours
	 *
	 * @return Item
	 */
	public function setHours($hours) {
		$this->hours = $hours;

		return $this;
	}

	/**
	 * Get section
	 *
	 * @return Section
	 */
	public function getSection() {
		return $this->section;
	}

	/**
	 * Set section
	 *
	 * @param Section $section
	 *
	 * @return Item
	 */
	public function setSection($section) {
		$this->section = $section;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Item
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}
}
