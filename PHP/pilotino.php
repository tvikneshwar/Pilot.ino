<?php

class PilotIno {
        public $fp=null;
        
        
        
        ///// ///// /////
        
        
        
        function dir($inOut,$pin) {
                if($inOut!='in') $inOut='out';
                else $inOut='in';
                return ($this->sendCmd('dir '.$inOut.' '.$pin))==='OK';
        }
        
        function set($digAnag,$pin,$val) {
                if($digAnag!='d') $digAnag='a';
                else $digAnag='d';
                return ($this->sendCmd('set '.$digAnag.' '.$pin.' '.$val)==='OK'); 
        }
        
        function get($digAnag,$pin) {
                if($digAnag!='d') $digAnag='a';
                else $digAnag='d';
                return $this->sendCmd('get '.$digAnag.' '.$pin);
        }
        
        
        
        ///// ///// /////
        
        
               
        function __construct($sport,$speed) {
                `stty -F $sport cs8 $speed ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts`;
                // `stty -F /dev/ttymxc3 cs8 57600 ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts`;
                
	        $this->fp =fopen($sport, "w+");
	        if( !$this->fp) {
	                echo "ERROR:CAN'T OPEN PORT\n";
	                // die();
	                return;
	        }
                stream_set_blocking($this->fp,0);
                stream_set_timeout($this->fp,10);
                // socket_set_option($this->fp, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>1, 'usec'=>0));
                // socket_set_option($this->fp, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>1, 'usec'=>0));
                // $this->fp=$fp;
        }
        function __destruct() {
                if($this->fp!=null && $this->fp) fclose($this->fp);
                $this->fp=null;
        }
        function sendCmd($cmd,$timeout=.3) {
           if($timeout==='' || $timeout===null) $timeout=0; // don't. Really.
           
           if($this->fp==null || !$this->fp) {
				echo "ERROR: PORT NOT OPEN \n";
				// die();
				return;
           }
           
           fflush($this->fp);
           $clear = fread($this->fp, 8192);

	        $stm=microtime(true);
	        fwrite($this->fp, ($cmd."\n"));
	        fflush($this->fp);
	        $ans='';
	        $rep='-';
	        
	        $loop=TRUE;
	        while($loop) {
                        do {
                                $rep = fread($this->fp, 8192);
                                $ans.=$rep;
                                if($rep!=='' && ($rep{strlen($rep)-1}==="\n" || $rep{strlen($rep)-1}==="\r")) {
                                        $loop=FALSE;
                                        break;
                                }
                                if($timeout>0 && (microtime(true)-$stm)>$timeout) {
                                        $loop=FALSE;
                                        $ans='ERROR:TIMEOUT:Data received: '.$ans;
                                        break;
                                }
                        } while (TRUE);
	        }
	        
	        return (trim($ans));
        }
}


?>
