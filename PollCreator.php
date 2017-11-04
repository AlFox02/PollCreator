<?php


class vote{
	private $error="";
	public function __construct(){}
	public function newPoll($poll_id, $creator){
		if(!$poll_id){ $this->error="Errore! Impossibile creare il poll perchè il nome del poll è mancante o non valido"; 
		} elseif(!$creator){ $this->error="Errore! Impossibile creare il poll perchè l'id dell'utente è mancante o non valido"; 
		} else {
			mkdir("Polls");
			mkdir("Polls/$creator");
			mkdir("Polls/$creator/$poll_id");
		}
	}
	public function addDescriptionPoll($poll_id, $creator, $description){
		if(!$poll_id){ $this->error="Errore! Impossibile modificare il poll perchè il nome del poll è mancante o non valido"; 
		} elseif(!creator){ $this->error="Errore! Impossibile modificare il poll perchè l'id del creatore è mancante o non valido"; 		
		} else {
			file_put_contents("Polls/$creator/$poll_id/description", $description);
		}		
	}
	public function cancelPoll($poll_id, $creator){
		if(!$poll_id){ $this->error="Errore! Impossibile cancellare il poll perchè il nome del poll è mancante o non valido"; 
		} elseif(!creator){ $this->error="Errore! Impossibile cancellare il poll perchè l'id del creatore è mancante o non valido"; 		
		} else {
			$this->ram("Polls/$creator/$poll_id");
		}
    }
	public function addChoicePoll($poll_id, $creator, $newChoice){
		if(!$poll_id){ $this->error="Errore! Impossibile aggiungere l'opzione perchè il nome del poll è mancante o non valido"; 
		} elseif(!creator){ $this->error="Errore! Impossibile aggiungere l'opzione perchè l'id del creatore è mancante o non valido"; 		
		} elseif(!$newChoice){ $this->error="Errore! Impossibile aggiungere l'opzione perchè l'opzione è mancante o non valida"; 
		} else {
			mkdir("Polls/$creator/$poll_id/$newChoice");
		}
	}
	public function removeChoicePoll($poll_id, $creator, $choice){
        $this->ram("Polls/$creator/$poll_id/$choice");		
	}
	public function addChoice($poll_id, $creator, $user_id, $choice){
		$q=scandir("Polls/$creator/$poll_id/$choice");
		foreach($q as $id){ if($id==$user_id) $hasAlreadyChosen=true; }
		if(!$hasAlreadyChosen){ file_put_contents("Polls/$creator/$poll_id/$choice/$user_id", " "); }
	}
	public function removeChoice($poll_id, $creator, $user_id, $choice){
		$q=unlink("Polls/$creator/$poll_id/$choice/$user_id");
	}
	public function removeAllChoices($poll_id, $creator, $user_id){
		$q=scandir("Polls/$creator/$poll_id");
		foreach($q as $choice){ unlink("Polls/$creator/$poll_id/$choice/$user_id"); }
	}
	public function sendPoll($poll_id, $creator){
		$description=file_get_contents("Polls/$creator/$poll_id/description");
		$c=scandir("Polls/$creator/$poll_id");
		$c3=$this->ariArray($c);
		$choice=$c3;
		$chosenChoice=array();
		foreach($choice as $optionableChoice){
			$chosenChoice[$optionableChoice]=count(scandir("Polls/$creator/$poll_id/$optionableChoice"))-2;
		}
		$result=array("poll_id"=>$poll_id, "creator"=>$creator, "description"=>$description, "choice"=>$chosenChoice);
		return json_encode($result);
	}
    public function getPollPercentage($poll_id, $creator){
		$c=scandir("Polls/$creator/$poll_id");
		$c3=$this->ariArray($c);
        $total=$this->getTotalChoice($poll_id, $creator);
        $result=array("poll_id"=>$poll_id, "creator"=>$creator, "total"=>$total);
        foreach($c3 as $choice){
        	$choiceCount=count(scandir("Polls/$creator/$poll_id/$choice"))-2;
            $result[0][$choice]=substr($choiceCount*100/$total, 0, 4);
        }	
        return json_encode($result);
    }
    public function getTotalChoice($poll_id, $creator){
		$c=scandir("Polls/$creator/$poll_id");
		$c3=$this->ariArray($c);
        $count=0;
        foreach($c3 as $choice){
        	$choiceCount=count(scandir("Polls/$creator/$poll_id/$choice"))-2;
            $count +=$choiceCount;
        }
        return $count;
    }
   	public function getTotalPoll($creator){
    	$result=array("creator"=>$creator);
        foreach($this->ariArray(scandir("Polls/$creator")) as $key=>$value){
        	$result["result"][$key]=$value;
        }
        return json_encode($result); 
    }	
    private function ari($arr,$item){
			if(in_array($item,$arr)){
				unset($arr[array_search($item,$arr)]); 
				return array_values($arr);
			}else{
				return $arr;
			}
		}
    public function ariArray($array){
		$c1=$this->ari($array, ".");
		$c2=$this->ari($c1, "..");
		$c3=$this->ari($c2, "description");   
        return $c3;
    }
	private function ram($dir){
		foreach(scandir($dir) as $file) {
			if ('.' === $file || '..' === $file) continue;
			if (is_dir($dir.'/'.$file)) ram($dir.'/'.$file);
			else unlink($dir.'/'.$file);
		}
		rmdir($dir);
	}	
	public function getError(){
		return $this->error;
	}
}


