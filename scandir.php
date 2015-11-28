<!DOCTYPE html>
<html lang="en">
  <head>
    <title>ScanDir</title>
  </head>
  <body>
<center>
<?php

function dirsize($dir) {
    if(is_file($dir)) return array('size'=>filesize($dir),'howmany'=>0);
    if($dh=opendir($dir)) {
        $size=0;
        $n = 0;
        while(($file=readdir($dh))!==false) {
            if($file=='.' || $file=='..') continue;
            $n++;
            $data = dirsize($dir.'/'.$file);
            $size += $data['size'];
            $n += $data['howmany'];
        }
        closedir($dh);
        return array('size'=>$size,'howmany'=>$n);
    } 
    return array('size'=>0,'howmany'=>0);
}

function file_size($fsizebyte) {
    if ($fsizebyte < 1024) {
        $fsize = $fsizebyte." bytes";
    }elseif (($fsizebyte >= 1024) && ($fsizebyte < 1048576)) {
        $fsize = round(($fsizebyte/1024), 2);
        $fsize = $fsize." KB";
    }elseif (($fsizebyte >= 1048576) && ($fsizebyte < 1073741824)) {
        $fsize = round(($fsizebyte/1048576), 2);
        $fsize = $fsize." MB";
    }elseif ($fsizebyte >= 1073741824) {
        $fsize = round(($fsizebyte/1073741824), 2);
        $fsize = $fsize." GB";
    }
    return $fsize;
}

if(isset($_REQUEST["path"])){
  $path=$_REQUEST["path"];
}else{
  $path="./";
}
$dir=scandir($path);
?>
<canvas id="myChart" width="400" height="400"></canvas>
<table border=1 width="600px">
	<tr>
        <td width="5%">&nbsp;</td>
		<td width="60%">File</td>
		<td width="15%">Typ</td>
		<td width="20%">Size</td>
	</tr>
	<?php

	foreach($dir as $value){
	  $file=$path."/".$value;
      if($value==="." || $value===".."){
        $icon="📂";
        $link="?path=$file";
        $name=$value;
        $type=filetype($file);
        $size=0;
      }else if(is_dir($file)){
        $icon="📂";
	  	$link="?path=$file";
	  	$name=$value;
	  	$type=filetype($file);
	  	$size=dirsize($file)["size"];

        $chartdata[]=@array(
            value=>$size,
            color=>"#".dechex(rand(0x000000, 0xFFFFFF)),
            label=> $name,
            path=>$path
        );
	 }else{
        $icon="📃";
		  	$link="?open=$file";
		  	$name=$value;
		  	$type=filetype($file);
		  	$size=filesize($file);
	 }
		?>
		<tr>
            <td><?php echo $icon;?></td>
			<td><?php echo 	"<a href=\"$link\">$name</a>";?> </td>
			<td><?php echo $type;?></td>
			<td><?php echo file_size($size);?></td> 	
		</tr>
		<?php 
	}
	?>
  </table>
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
  <script>
var options={
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke : true,

    //String - The colour of each segment stroke
    segmentStrokeColor : "#fff",

    //Number - The width of each segment stroke
    segmentStrokeWidth : 2,

    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout : 50, // This is 0 for Pie charts

    //Number - Amount of animation steps
    animationSteps : 100,

    //String - Animation easing effect
    animationEasing : "easeOutBounce",

    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate : true,

    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale : false,

    //String - A legend template
    legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",

tooltipTemplate: "<%=label%> (<%=Math.round(value/1048576)%> MB)"
};

  var data=<?php echo json_encode($chartdata);?>;
  var myPieChart = new Chart(document.getElementById("myChart").getContext("2d")).Pie(data,options);


$("#myChart").click( 
                        function(evt){
                            var activePoints = myPieChart.getSegmentsAtEvent(evt);
                            var url = "scandir.php?path=<?php echo $path;?>/"+activePoints[0].label;
                            console.log(activePoints[0]);
                           location.href=url;
                        }
                    );             
  </script>
</center>
</body>
</html>