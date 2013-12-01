<?php
namespace CCV\Entity;

/**
 * @Entity
 * @Table(name="ticket_types")
 */
class TicketType {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @Column(type="string",unique=true)
	 */
	private $slug;

	/**
	 * @Column(type="integer")
	 */
	private $price;

	/**
	 * @Column(type="boolean")
	 */
	private $active;

	/**
	 * @Column(type="string", name="receiver")
	 */
	private $to;

	/**
     * @OneToMany(targetEntity="Ticket", mappedBy="type")
     **/
	private $tickets;

	public function __construct() {
		$this->active = true;
		$this->tickets = new \Doctrine\Common\Collections\ArrayCollection();
	}


	public function getId() {
	    return $this->id;
	}
	
	public function setId($id) {
	    $this->id = $id;
	
	    return $this;
	}

	public function getSlug() {
	    return $this->slug;
	}
	
	public function setSlug($slug) {
	    $this->slug = $slug;
	
	    return $this;
	}

	public function getPrice() {
	    return $this->price;
	}
	
	public function setPrice($price) {
	    $this->price = $price;
	
	    return $this;
	}

	public function isActive() {
	    return $this->active;
	}
	
	public function setActive($active) {
	    $this->active = $active;
	
	    return $this;
	}

	public function getTickets() {
	    return $this->tickets;
	}
	
	public function setTickets($tickets) {
	    $this->tickets = $tickets;
	
	    return $this;
	}

	public function addTicket(Ticket $ticket) {
		$this->tickets->add($ticket);
	}

	public function removeTicket(Ticket $ticket) {
		$this->tickets->remove($ticket);
	}

	public function getTo() {
	    return $this->to;
	}
	
	public function setTo($to) {
	    $this->to = $to;
	
	    return $this;
	}
}