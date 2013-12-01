<?php
namespace CCV\Entity;

/**
 * @Entity
 * @Table(name="tickets")
 */
class Ticket {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue(strategy="NONE")
	 */
	private $id;

	/**
	 * @Column(type="datetime")
	 */
	private $creation_time;

	/**
	 * @Column(type="datetime", nullable=true)
	 */
	private $use_time;

	/**
	 * @Column(type="boolean")
	 */
	private $used;

	/**
     * @ManyToOne(targetEntity="TicketType", inversedBy="tickets")
     * @JoinColumn(name="type_id", referencedColumnName="id")
     **/
	private $type;

	public function __construct() {
		$this->creation_time = new \DateTime();
		$this->use_time = null;
		$this->used = false;
	}


	public function getId() {
	    return $this->id;
	}
	
	public function setId($id) {
	    $this->id = $id;
	
	    return $this;
	}


	public function isUsed() {
	    return $this->used;
	}
	
	public function setUsed($used) {
	    $this->used = $used;
	
	    return $this;
	}


	public function getCreationTime() {
	    return $this->creation_time;
	}
	
	public function setCreationTime($creation_time) {
	    $this->creation_time = $creation_time;
	
	    return $this;
	}


	public function getUseTime() {
	    return $this->use_time;
	}
	
	public function setUseTime($use_time) {
	    $this->use_time = $use_time;
	
	    return $this;
	}


	public function getType() {
	    return $this->type;
	}
	
	public function setType(TicketType $type) {
		if ($this->type instanceof TicketType) {
			$this->type->removeTicket($this);
		}
	    $this->type = $type;
		$this->type->addTicket($this);	
	    return $this;
	}
}