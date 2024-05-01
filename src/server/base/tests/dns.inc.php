<?php // FreeNATS DNS Test Module
/* -------------------------------------------------------------
This file is the PurplePixie PHP DNS Query Classes

The software is (C) Copyright 2008 PurplePixie Systems

This is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

The software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this software.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/phpdns
-------------------------------------------------------------- */

//
// Version 0.02		20th June 2008
// David Cutting -- dcutting (some_sort_of_at_sign) purplepixie dot org
//

class DNSTypes
{
	var $types_by_id;
	var $types_by_name;
	
	function AddType($id,$name)
	{
		$this->types_by_id[$id]=$name;
		$this->types_by_name[$name]=$id;
	}
	
	function DNSTypes()
	{
		$this->types_by_id=array();
		$this->types_by_name=array();
		
		$this->AddType(1,"A");
		$this->AddType(2,"NS");
		$this->AddType(5,"CNAME");
		$this->AddType(6,"SOA");
		$this->AddType(12,"PTR");
		$this->AddType(15,"MX");
		$this->AddType(16,"TXT");
		$this->AddType(255,"ANY");
	}

	function GetByName($name)
	{
		if (isset($this->types_by_name[$name])) return $this->types_by_name[$name];
		return 0;
	}
		
	function GetById($id)
	{
		if (isset($this->types_by_id[$id])) return $this->types_by_id[$id];
		return "";
	}
}

class DNSResult
{
	var $type;
	var $typeid;
	var $class;
	var $ttl;
	var $data;
	var $domain;
	var $string;
	var $extras=array();
}

class DNSAnswer
{
	var $count=0;
	var $results=array();
	
	function AddResult($type,$typeid,$class,$ttl,$data,$domain="",$string="",$extras=array())
	{
		$this->results[$this->count]=new DNSResult();
		$this->results[$this->count]->type=$type;
		$this->results[$this->count]->typeid=$typeid;
		$this->results[$this->count]->class=$class;
		$this->results[$this->count]->ttl=$ttl;
		$this->results[$this->count]->data=$data;
		$this->results[$this->count]->domain=$domain;
		$this->results[$this->count]->string=$string;
		$this->results[$this->count]->extras=$extras;
		$this->count++;
		return ($this->count-1);
	}
}
		

class DNSQuery
{
	var $server="";
	var $port;
	var $timeout;
	var $udp;
	var $debug;
	var $binarydebug=false;
	
	var $types;
	
	var $rawbuffer="";
	var $rawheader="";
	var $rawresponse="";
	var $header;
	var $responsecounter=0;
	
	var $lastnameservers;
	var $lastadditional;
	
	var $error=false;
	var $lasterror="";
	
	function ReadResponse($count=1,$offset="")
	{
		if ($offset=="") // no offset so use and increment the ongoing counter
			{
			$return=substr($this->rawbuffer,$this->responsecounter,$count);
			$this->responsecounter+=$count;
			}
		else
			{
			$return=substr($this->rawbuffer,$offset,$count);
			}
		return $return;
	}
	
	function ReadDomainLabels($offset,&$counter=0)
	{
		$labels=array();
		$startoffset=$offset;
		$return=false;
		while (!$return)
			{
			$label_len=ord($this->ReadResponse(1,$offset++));
			if ($label_len<=0) $return=true; // end of data
			else if ($label_len<64) // uncompressed data
				{
				$labels[]=$this->ReadResponse($label_len,$offset);
				$offset+=$label_len;
				}
			else // label_len>=64 -- pointer
				{
				$nextitem=$this->ReadResponse(1,$offset++);
				$pointer_offset = ( ($label_len & 0x3f) << 8 ) + ord($nextitem);
				// Branch Back Upon Ourselves...
				$this->Debug("Label Offset: ".$pointer_offset);
				$pointer_labels=$this->ReadDomainLabels($pointer_offset);
				foreach($pointer_labels as $ptr_label)
					$labels[]=$ptr_label;
				$return=true;
				}
			}
		$counter=$offset-$startoffset;
		return $labels;
	}
	
	function ReadDomainLabel()
	{
		$count=0;
		$labels=$this->ReadDomainLabels($this->responsecounter,$count);
		$domain=implode(".",$labels);
		$this->responsecounter+=$count;
		$this->Debug("Label ".$domain." len ".$count);
		return $domain;
	}
	
	function Debug($text)
	{
		if ($this->debug) echo $text."\n";
	}
	
	function DebugBinary($data)
	{
		if ($this->binarydebug)
		{
			for ($a=0; $a<strlen($data); $a++)
			{
				echo $a;
				echo "\t";
				printf("%d",$data[$a]);
				echo "\t";
				$hex=bin2hex($data[$a]);
				echo "0x".$hex;
				echo "\t";
				$dec=hexdec($hex);
				echo $dec;
				echo "\t";
				if (($dec>30)&&($dec<150)) echo $data[$a];
				echo "\n";
			}
		}
	}
	
	function SetError($text)
	{
		$this->error=true;
		$this->lasterror=$text;
		$this->Debug("Error: ".$text);
	}
	
	function ClearError()
	{
		$this->error=false;
		$this->lasterror="";
	}
	
	function DNSQuery($server,$port=53,$timeout=60,$udp=true,$debug=false)
	{
		$this->server=$server;
		$this->port=$port;
		$this->timeout=$timeout;
		$this->udp=$udp;
		$this->debug=$debug;
		
		$this->types=new DNSTypes();
		$this->Debug("DNSQuery Class Initialised");
	}
	
	
	function ReadRecord()
	{
		// First the pesky domain names - maybe not so pesky though I suppose
			
		$domain=$this->ReadDomainLabel(); 
			
	
			
		$ans_header_bin=$this->ReadResponse(10); // 10 byte header
		$ans_header=unpack("ntype/nclass/Nttl/nlength",$ans_header_bin);
		$this->Debug("Record Type ".$ans_header['type']." Class ".$ans_header['class']." TTL ".$ans_header['ttl']." Length ".$ans_header['length']);
	
		$typeid=$this->types->GetById($ans_header['type']);
		$extras=array();
		$data="";
		$string="";

		switch($typeid)
			{
			case "A":
				$ip=implode(".",unpack("Ca/Cb/Cc/Cd",$this->ReadResponse(4)));
				$data=$ip;
				$string=$domain." has address ".$ip;
				break;
				
			case "NS":
				$nameserver=$this->ReadDomainLabel();
				$data=$nameserver;
				$string=$domain." nameserver ".$nameserver;
				break;
				
			case "PTR":
				$data=$this->ReadDomainLabel();
				$string=$domain." points to ".$data;
				break;
				
			case "CNAME":
				$data=$this->ReadDomainLabel();
				$string=$domain." alias of ".$data;
				break;
				
			case "MX":
				$prefs=$this->ReadResponse(2);
				$prefs=unpack("nlevel",$prefs);
				$extras['level']=$prefs['level'];
				$data=$this->ReadDomainLabel();
				$string=$domain." mailserver ".$data." (pri=".$extras['level'].")";
				break;
				
			case "SOA":
				// Label First
				$data=$this->ReadDomainLabel();
				$responsible=$this->ReadDomainLabel();
				
				$buffer=$this->ReadResponse(20);
				$extras=unpack("nserial/nrefresh/Nretry/Nexpiry/Nminttl",$buffer);
				$dot=strpos($responsible,".");
				$responsible[$dot]="@";
				$extras['responsible']=$responsible;
				$string=$domain." SOA ".$data." Serial ".$extras['serial'];
				break;
		
			case "TXT":
				$data=$this->ReadResponse($ans_header['length']);
				$string=$domain." TEXT ".$data;
				break;
				
			default: // something we can't deal with
				$stuff=$this->ReadResponse($ans_header['length']);
				break;
				
			}
	
		//$dns_answer->AddResult($ans_header['type'],$typeid,$ans_header['class'],$ans_header['ttl'],$data,$domain,$string,$extras);
		$return=array(
			"header" => $ans_header,
			"typeid" => $typeid,
			"data" => $data,
			"domain" => $domain,
			"string" => $string,
			"extras" => $extras );
		return $return;
	}

		
	
	
	
	
	function Query($question,$type="A")
	{
		$this->ClearError();
		$typeid=$this->types->GetByName($type);
		if ($typeid===false)
			{
			$this->SetError("Invalid Query Type ".$type);
			return false;
			}
			
		if ($this->udp) $host="udp://".$this->server;
		else $host=$this->server;
		
		if (!$socket=fsockopen($host,$this->port,$this->timeout))
			{
			$this->SetError("Failed to Open Socket");
			return false;
			}
			
		// Split Into Labels
		if (preg_match("/[a-z|A-Z]/",$question)==0) // IP Address
			{
			$labeltmp=explode(".",$question);	// reverse ARPA format
			for ($i=count($labeltmp)-1; $i>=0; $i--)
				$labels[]=$labeltmp[$i];
			$labels[]="IN-ADDR";
			$labels[]="ARPA";
			}
		else // hostname
			$labels=explode(".",$question);

		$question_binary="";
		for ($a=0; $a<count($labels); $a++)
			{
			$size=strlen($labels[$a]);
			$question_binary.=pack("C",$size); // size byte first
			$question_binary.=$labels[$a]; // then the label
			}
		$question_binary.=pack("C",0); // end it off
		
		$this->Debug("Question: ".$question." (type=".$type."/".$typeid.")");
		
		$id=rand(1,255)|(rand(0,255)<<8);	// generate the ID
		
		// Set standard codes and flags
		$flags=0x0100 & 0x0300; // recursion & queryspecmask
		$opcode=0x0000; // opcode
		
		// Build the header
		$header="";
		$header.=pack("n",$id);
		$header.=pack("n",$opcode | $flags);
		$header.=pack("nnnn",1,0,0,0);
		$header.=$question_binary;
		$header.=pack("n",$typeid);
		$header.=pack("n",0x0001); // internet class
		$headersize=strlen($header);
		$headersizebin=pack("n",$headersize);
		
		$this->Debug("Header Length: ".$headersize." Bytes");
		$this->DebugBinary($header);
		
		if ( ($this->udp) && ($headersize>=512) )
			{
			$this->SetError("Question too big for UDP (".$headersize." bytes)");
			fclose($socket);
			return false;
			}
			
		if ($this->udp) // UDP method
			{
			if (!fwrite($socket,$header,$headersize))
				{
				$this->SetError("Failed to write question to socket");
				fclose($socket);
				return false;
				}
			if (!$this->rawbuffer=fread($socket,4096)) // read until the end with UDP
				{
				$this->SetError("Failed to write read data buffer");
				fclose($socket);
				return false;
				}				
			}
		else // TCP
			{
			if (!fwrite($socket,$headersizebin)) // write the socket
				{
				$this->SetError("Failed to write question length to TCP socket");
				fclose($socket);
				return false;
				}
			if (!fwrite($socket,$header,$headersize))
				{
				$this->SetError("Failed to write question to TCP socket");
				fclose($socket);
				return false;
				}
			if (!$returnsize=fread($socket,2))
				{
				$this->SetError("Failed to read size from TCP socket");
				fclose($socket);
				return false;
				}
			$tmplen=unpack("nlength",$returnsize);
			$datasize=$tmplen['length'];
			$this->Debug("TCP Stream Length Limit ".$datasize);
			if (!$this->rawbuffer=fread($socket,$datasize))
				{
				$this->SetError("Failed to read data buffer");
				fclose($socket);
				return false;
				}
			}
		fclose($socket);
		
		$buffersize=strlen($this->rawbuffer);
		$this->Debug("Read Buffer Size ".$buffersize);
		
		if ($buffersize<12)
			{
			$this->SetError("Return Buffer too Small");
			return false;
			}
			
		$this->rawheader=substr($this->rawbuffer,0,12); // first 12 bytes is the header
		$this->rawresponse=substr($this->rawbuffer,12); // after that the response
		
		$this->responsecounter=12; // start parsing response counter from 12 - no longer using response so can do pointers
		
		$this->DebugBinary($this->rawbuffer);
		
		$this->header=unpack("nid/nspec/nqdcount/nancount/nnscount/narcount",$this->rawheader);
		
		$answers=$this->header['ancount'];
		$this->Debug("Query Returned ".$answers." Answers");
		
		$dns_answer=new DNSAnswer();
		
		// Deal with the header question data
		if ($this->header['qdcount']>0)
			{
			$this->Debug("Found ".$this->header['qdcount']." Questions");
			for ($a=0; $a<$this->header['qdcount']; $a++)
				{
				$c=1;
				while ($c!=0)
					{
					$c=hexdec(bin2hex($this->ReadResponse(1)));
					}
				$extradata=$this->ReadResponse(4);
				}
			}

		// New Functional Method
		for ($a=0; $a<$this->header['ancount']; $a++)
			{
			$record=$this->ReadRecord();
			$dns_answer->AddResult($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
				$record['data'],$record['domain'],$record['string'],$record['extras']);
			}
			
		$this->lastnameservers=new DNSAnswer();
		for ($a=0; $a<$this->header['nscount']; $a++)
			{
			$record=$this->ReadRecord();
			$this->lastnameservers->AddResult($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
				$record['data'],$record['domain'],$record['string'],$record['extras']);
			}
			
		$this->lastadditional=new DNSAnswer();
		for ($a=0; $a<$this->header['arcount']; $a++)
			{
			$record=$this->ReadRecord();
			$this->lastadditional->AddResult($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
				$record['data'],$record['domain'],$record['string'],$record['extras']);
			}
			

				
		
	return $dns_answer; 
	}
	
	
function SmartALookup($hostname,$depth=0)
	{
	$this->Debug("SmartALookup for ".$hostname." depth ".$depth);
	if ($depth>5) return ""; // avoid recursive lookups
	// The SmartALookup function will resolve CNAMES using the additional properties if possible
	$answer=$this->Query($hostname,"A");
	
	if ($answer===false) return "";		// failed totally
	if ($answer->count<=0) return "";	// no records at all returned
	
	$best_answer="";
	$best_answer_typeid=0;
	
	$records=$answer->count;
	for ($a=0; $a<$records; $a++)
		{
		$data=$answer->results[$a]->data;
		$answer_typeid=$answer->results[$a]->typeid;
		
		if ($answer_typeid=="A") // found it
			{
			$best_answer=$data;
			$best_answer_typeid="A";
			$a=$records+10;
			}
		else if ($answer_typeid=="CNAME") // alias
			{
			$best_answer=$data;
			$best_answer_typeid="CNAME";
			// and keep going
			}
			
		}
		
	if ( ($best_answer=="") || ($best_answer_typeid=="") ) return "";
	
	if ($best_answer_typeid=="A") return $best_answer; // got an IP ok
	
	if ($best_answer_typeid!="CNAME") return ""; // shouldn't ever happen
	
	$newtarget=$best_answer; // this is what we now need to resolve
	
	// First is it in the additional section
	for ($a=0; $a<$this->lastadditional->count; $a++)
		{
		if ( ($this->lastadditional->results[$a]->domain==$hostname) &&
			($this->lastadditional->results[$a]->typeid=="A") )
				return $this->lastadditional->results[$a]->data;
		}
		
	// Not in the results
	
	return $this->SmartALookup($newtarget,++$depth);
	}
		
}

?>