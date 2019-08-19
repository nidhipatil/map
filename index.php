
<?php
    
    $conn=mysqli_connect('localhost','root','','map'); //connection with database
    if($conn){
      echo "connected";
    }
    else{
      echo $conn->error;
    }


  if(isset($_POST['locate'])){
    $lat=$_POST['lat'];
    $longi=$_POST['longi'];
    $zoom=$_POST['zoom'];
    $imgpath=$_FILES['image']['tmp_name'];
    if($imgpath){
       $img_binary = fread(fopen($imgpath, "r"), filesize($imgpath));
       $picture = base64_encode($img_binary);

       $insert=mysqli_query($conn,"INSERT INTO locate (lat,longi,zoom,image) VALUES ('$lat','$longi','$zoom','$picture')");

     if($insert){
               //echo "inserted successfully";
        echo"<script language='javascript'>";
               // echo'document.location.replace("./location.php")';
        echo"</script>";
      }else{
        echo $conn->error;
       }
    }else{
    //  echo "insert image";
    }
  }
  ?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>

<div class="main">
  <form class="container" method = "POST" action = "" enctype="multipart/form-data" >
    Lat : <input type = "text" name = "lat">
    long : <input type = "text" name = "longi">
    Zoom : <input type = "text" name = "zoom">
    <input type="file"  name = "image" >
    <input type = "submit" name = "locate" value = "Locate">
  </form>
</div>

  <div id="mapdiv"></div>
</html>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/openlayers/2.11/lib/OpenLayers.js"></script>
  <script>
    map = new OpenLayers.Map("mapdiv");
    map.addLayer(new OpenLayers.Layer.OSM());

    epsg4326 =  new OpenLayers.Projection("EPSG:4326"); 
    projectTo = map.getProjectionObject(); 

    var lonLat = new OpenLayers.LonLat(73.856255,18.516726).transform(epsg4326, projectTo);
    var zoom=9;
    map.setCenter (lonLat, zoom);
    var vectorLayer = new OpenLayers.Layer.Vector("Overlay");

    <?php 
      $select=mysqli_query($conn,"SELECT * FROM locate");
           $row=mysqli_num_rows($select);
             if($row){
              while($result=mysqli_fetch_assoc($select)){
              $id=$result['id'];
              $lat=$result['lat'];
              $longi=$result['longi'];
              $zoom=$result['zoom']; 
     ?>
    // Define markers as "features" of the vector layer:
    var feature = new OpenLayers.Feature.Vector(
            new OpenLayers.Geometry.Point( <?php echo $lat;?>, <?php echo $longi;?> ).transform(epsg4326, projectTo),
            {description:'<?php echo $lat."<br> ".$longi;?>'} ,
            {externalGraphic: 'marker.png', graphicHeight: 25, graphicWidth: 21, graphicXOffset:-12, graphicYOffset:-25  }
		        );
		    vectorLayer.addFeatures(feature);
		   <?php
		                    }
		    }
       ?>

    map.addLayer(vectorLayer);
    //Add a selector control to the vectorLayer with popup functions
    var controls = {
      selector: new OpenLayers.Control.SelectFeature(vectorLayer, { onSelect: createPopup, onUnselect: destroyPopup })
    };

    function createPopup(feature) {
      feature.popup = new OpenLayers.Popup.FramedCloud("pop",
          feature.geometry.getBounds().getCenterLonLat(),
          null,
          '<div class="markerContent">'+feature.attributes.description+'</div>',
          null,
          true,
          function() { controls['selector'].unselectAll(); }
	      );
	      map.addPopup(feature.popup);
	    }

	 function destroyPopup(feature) {
	      feature.popup.destroy();
	      feature.popup = null;
	    }

	   map.addControl(controls['selector']);
	    controls['selector'].activate();

  </script>