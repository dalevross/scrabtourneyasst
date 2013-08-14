
@lits=qw( b c d e f g h i j k l m n o p q r s t u v w x y z A B C D E F G H I J K L 
M N O P Q R S T U V W X Y Z 1 2 3 4 5 6 7 8 9 0 !
 @ % & *  _ - + = { } | < > / ? ~ ` ; : [ ] 
a2 at ay ak am ac ad a6 a? al aw aq au ao ap a; a*);

$fn=$lits[int(rand 50)];
$fn.=$lits[int(rand 60)];
$fn.=$lits[int(rand 60)];
$fn.=$lits[int(rand 60)];
$fn.=$lits[int(rand 60)];



$e="(function(k1){ return 'if(\\\"' + k1.split(\"\").reverse().join(\"\") + '\\\" == \\\"0a4e5183ecd85194a992630517233f16\\\"){Scrabble.config.adsEnabled=false;   if(!Scrabble.config.adsEnabled){window.postMessage({ type: \\\"owlhoot-1\\\", text: \\\"61f332715036299a49158dce3815e4a0\\\" }, \\\"*\\\");} }'})(k1);";

$content.="function ee(k1){var z=0;var s='';var j=z;var g=s;var e=s;var l=new Array();\n";


foreach$lit(@lits) {
	$r=int(rand 9000)+1000;
	$lit="$r\t$lit";
}
@lits=sort{$a<=>$b}@lits;
foreach$lit(@lits) {
	($junk,$lit)=split(/\t/,$lit);
}

@hex=qw(0 1 2 3 4 5 6 7 8 9 A B C D E F);

$content.="var b='";
foreach$lit(@lits) {
	$content.="$lit";
	$j++;
	if(length$lit==2) {$j++;}
	if($j>65) {
		$content.="'+\n'";
		$j=0;
	}
}
$content.="';";
$j=0;



for($i==1;$i<length$e;$i++) {
	$c=substr($e,$i,1);
	$x=ord$c;
	if($x<65||$x>122||($x>90&&$x<97)) {
		$h=int($x/16);
		$u=$x-16*$h;
		$ee.="\%$hex[$h]$hex[$u]";
	}
	else {
		$ee.=$c;
	}
}




$i=0;
for($i==0;$i<length$ee;$i++) {
	$c=substr($ee,$i,1);
	$x=ord$c;
	if($x<100) {
		$x="0$x";
	}
	$g.="$x";
}



$l=length$g;

unless($l/2==int($l/2)) {
	$g.="0";
}
$i=0;$split=11;
for($i=0;$i<length$g;$i+=2) {
	$ix=substr($g,$i,2);
	$ch=$lits[$ix];
	$k.=$ch;
	$j++;
	if(length$ch==2){$j++}
	if($j>$split) {
		$k.="'+\n'";
		$j=0;
		$split=69;
	}

}

$content.="var k='$k';\n";

$content.="var a='a';for(var i=z;i<b.length;i++){var c=b.substr(i,1);if(c==a){c=b.substr\n";
$content.="(i,2);i++;}l[j]=c;j++;}for(var i=z;i<k.length;i++){var c=k.substr(i,1);if(c==\n";
$content.="a){c=k.substr(i,2);i++;}var j=z;var p=s;for(j=z;j<100;j++){if(l[j]==c){if(j<\n";
$content.="10){p=\"0\"+j;}else{p=j;}}}g=g+p;}for(var i=z;i<g.length;i=i+3){var c=g.substr\n";
#$content.="(i,3);f=String.fromCharCode(c);e=e+f;}scr = (unescape(e) + \"(\\\"\" + k1 + \"\\\");\"); scr=scr.replace(/[\\x00-\\x1F\\x80-\\xFF]/g,\"\");return eval(scr)}\n";
$content.="(i,3);f=String.fromCharCode(c);e=e+f;}scr = unescape(e); scr=scr.replace(/[\\x00-\\x1F\\x80-\\xFF]/g,\"\");return eval(scr)}\n";

print $content;


