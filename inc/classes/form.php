<?php
class form
{
  // The inputs of the form as an array of inputs
  private const Input $inputs;

  // Constructor
  function __construct($reqInputs)
  {
	  $this->inputs = $reqInputs;
  }

  // Validate all inputs. Returns true if all valid, false else
  public function validateInputs()
  {
    foreach($this->value as $input)
    {
      if(!$input->validate())
      {
        return 0;
      }
    }
    return 1;
  }
  
  // 

}


?>