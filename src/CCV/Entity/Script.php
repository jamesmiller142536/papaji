<?php
namespace CCV\Entity;

use CCV\Util\StringUtil;

/**
 * @Entity
 * @Table(name="scripts")
 */
class Script {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @Column(type="string")
	 */
	private $name;

	/**
	 * @Column(type="string")
	 */
	private $slug;

	/**
	 * @Column(type="text")
	 */
	private $contents;


	public function getId() {
	    return $this->id;
	}
	
	public function setId($id) {
	    $this->id = $id;
	
	    return $this;
	}


	public function getName() {
	    return $this->name;
	}
	
	public function setName($name) {
	    $this->name = $name;
	    $this->setSlug($this->generateSlug($name));
	
	    return $this;
	}


	public function getSlug() {
	    return $this->slug;
	}
	
	protected function setSlug($slug) {
	    $this->slug = $slug;
	
	    return $this;
	}


	public function getContents() {
	    return $this->contents;
	}
	
	public function setContents($contents) {
	    $this->contents = $contents;
	
	    return $this;
	}

	public function getFunctions() {
		preg_match_all('/(\r?\n)function.*/', $this->contents, $matches);
		$matches = array_map(function($item) {
			return trim($item);
		}, $matches[0]);
		if (strpos($this->contents, 'function') === 0) {
			array_unshift($matches, trim(substr($this->contents, 0, strpos($this->contents, "\n"))));
		}
		$functions = array();
		foreach ($matches as $match) {
			$function = array('name' => '', 'args' => array(), 'types' => array());
			$pos = strpos($match, '(');
			$function['name'] = substr($match, 9, $pos - 9);
			$args = trim(substr($match, $pos), '()');
			if (strlen($args) !== 0) {
				$args = explode(',', $args);
				$args = array_map(function($item) {
					return trim(trim($item), '()');
				}, $args);
				$function['args'] = $args;
				$function['types'] = array_map(function($item) {
					$type = 'mixed';
					if (ctype_upper(substr($item, 1, 1))) {
						switch (substr($item, 0, 1)) {
							case 'i':
								$type = 'integer';
								break;
							case 's':
								$type = 'string';
								break;
							case 'b':
								$type = 'boolean';
								break;
							case 'a':
								$type = 'array';
								break;
							case 't':
								$type = 'table';
								break;
							default:
								$type = 'mixed';
								break;
						}
					}
					return $type;
				}, $args);
			}
			$functions[] = $function;
		}
		return $functions;
	}

	public function getStrippedName() {
        return StringUtil::strip($this->name);
    }

	protected function generateSlug($slug) {
        return StringUtil::slug($slug);
    }
}