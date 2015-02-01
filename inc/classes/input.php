<?php
class Input
{
  // The content of the input
  private $value;
  private $name;

  // Constructor
  function __construct($reqName, $reqValue)
  {
    $this->name = $reqName;
    $this->value = $reqValue;
  }

  // Validate for mysql Injection. Returns true if valid, false else
  public function validate()
  {
    $copyOfValue = htmlentities($this->value);
    return ($copyOfValue == $this->value) ? 1 : 0;
  }

  // Returns the value of the input
  public function getValue()
  {
    return $this->value;
  }
}



?>