<?php
include("includes/init.php");

const MAX_FILE_SIZE = 1000000;


function gallery($a_image){
  ?>
    <figure>
      <a href="index.php?<?php echo http_build_query(array('image_single_id' => strtolower($a_image['id']))); ?>">
      <!-- Source: (original work) Stephanie Chow -->
      <img class="display" src="uploads/images/<?php echo $a_image['id'].$a_image['file_ext']; ?>" name = <?php echo $a_image['id']?>  alt="<?php echo $a_image['name']; ?>" /> </a>
    </figure>
<?php
}

function gallery_single($a_image){
  ?>
    <figure>
      <a href="index.php?<?php echo http_build_query(array('image_single_id' => strtolower($a_image['id']))); ?>">
      <!-- Source: (original work) Stephanie Chow -->
      <img  src="uploads/images/<?php echo $a_image['id'].$a_image['file_ext']; ?>" name = <?php echo $a_image['id']?>  alt="<?php echo $a_image['name']; ?>" /> </a>
    </figure>
  <?php

}

function last_image($current_id,$db){
  $params= array();
  $result = exec_sql_query($db,"SELECT MAX(id) as max_id FROM images", $params) -> fetchAll();
  foreach ($result as $i){
    $highest = $i['max_id'];
  }
  if ($current_id == '1'){
     return $highest;
  }
  else{
    return $current_id-1;
  }
}

function next_image($current_id,$db){
  $params= array();
  $result = exec_sql_query($db,"SELECT MAX(ID) as max_id FROM images", $params) -> fetchAll();
  foreach ($result as $i){
    $highest = $i['max_id'];
  }
  if ($current_id == $highest){
    return 1;
  }
  else{
    return $current_id+1;
  }
}


$tags = exec_sql_query($db,"SELECT tags.name FROM tags;", $params) -> fetchAll();

//Search Form
$do_search = FALSE;
$single_image = FALSE;

if (isset($_GET['type'])) {
  $search_field = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
  if($search_field !="All"){
    $do_search = TRUE;
  }
  $search_field = trim($search_field);
}

//Upload form

if (isset($_POST["submit_upload"])) {
  $good_upload = FALSE;
  $upload_info = $_FILES['upload_file'];

  if ($upload_info['error'] == UPLOAD_ERR_OK){
    if($upload_info['size']<MAX_FILE_SIZE){
      if($upload_info['type'].include('image'))
      $good_upload = TRUE;
      else{
        echo "Incorrect upload type -- Please only upload images";
      }
    }
    else{
      echo "The file is too big!";
    }
  }
  else{
    echo "The file failed to be uploaded!";
  }
  $upload_title = filter_input(INPUT_POST, 'upload_title', FILTER_SANITIZE_STRING);
  $upload_uploader = filter_input(INPUT_POST, 'upload_uploader', FILTER_SANITIZE_STRING);
  $file_description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
  $upload_tags = $_POST['upload_tags_have'];

  $need=array();
  foreach($tags as $t){
    array_push($need, $t['name']);
  }

  foreach($upload_tags as $upload_a_tag){
    if (in_array($upload_a_tag, array_values($need)) == FALSE) {
      echo "Invalid tag.";
      $good_upload= FALSE;
    }
  }


  if($good_upload){

    $basename = basename($upload_info["name"]);
    $upload_ext = strtolower( pathinfo($basename, PATHINFO_EXTENSION) );

    $sql = "INSERT INTO images (name, file_ext, uploader, description) VALUES ( :upload_title, :upload_ext, :upload_uploader, :file_description)";
    $params = array(
        ':upload_title' => $upload_title,
        ':upload_ext' => '.' . $upload_ext,
        ':upload_uploader' => $upload_uploader,
        ':file_description' => $file_description
    );
    exec_sql_query($db, $sql, $params);

    $upload_id= $db->lastInsertId("id");
    var_dump($upload_tags);
    foreach($upload_tags as $upload_a_tag){
      $sql = "SELECT tags.id FROM tags WHERE tags.name= :upload_a_tag";
      $params= array(
        ':upload_a_tag' => $upload_a_tag
      );
      $result= exec_sql_query($db, $sql, $params) -> fetchAll();
      foreach($result as $r){
        $tag_id = $r['id'];

        $sql="INSERT INTO image_tags(image_id, tag_id) VALUES (:upload_id,:tag_id)";
        $params = array(
          ':upload_id' => $upload_id,
          ':tag_id' => $tag_id
        );

        exec_sql_query($db, $sql, $params);
        }

    }
    $new_path = "uploads/images/" . $upload_id . '.' . $upload_ext;
    move_uploaded_file( $upload_info["tmp_name"], $new_path );

  }
}
//Delete form

$deleted = false;
if (isset($_POST["delete_image"])) {

    $current_id = filter_input(INPUT_POST, 'delete_current_id', FILTER_SANITIZE_NUMBER_INT);

    $sql = "SELECT file_ext FROM images WHERE images.id = :single_image_id";
    $params = array(
      ':single_image_id' => $current_id
    );
    $result = exec_sql_query($db, $sql, $params) -> fetchAll();
    foreach($result as $r)
    $current_ext = $r['file_ext'];


    $sql = "DELETE FROM images WHERE images.id = :single_image_id";
    $params = array(
      ':single_image_id' => (int) $current_id
    );
    exec_sql_query($db, $sql, $params);

    $sql = "DELETE FROM image_tags WHERE image_tags.image_id= :image_id";
    $params = array(
      ':image_id' => $current_id
    );
    exec_sql_query($db, $sql, $params);

    unlink("uploads/images/" . $current_id . $current_ext);
    $deleted = true;
}

//Add Tag form
$added = false;
if (isset($_POST["add_tag"])) {

  $current_id = filter_input(INPUT_POST, 'add_current_id', FILTER_SANITIZE_NUMBER_INT);
  $tag = filter_input(INPUT_POST, 'tagToAdd', FILTER_SANITIZE_STRING);

  $need=array();
  foreach($tags as $t){
    array_push($need, $t['name']);
  }
  if (in_array($tag, array_values($need)) == TRUE) {
    $sql = "SELECT tags.id FROM tags WHERE tags.name = :tag_name";
    $params = array(
      ':tag_name' => $tag,
  );
  $result = exec_sql_query($db, $sql, $params) -> fetchAll();foreach ($result as $i){
    $tag_id = $i['id'];
  }

  $sql = "INSERT INTO image_tags (image_id, tag_id) VALUES ( :image_id, :tag_id)";
  $params = array(
   ':image_id' => $current_id,
   ':tag_id' => $tag_id
  );
    exec_sql_query($db, $sql, $params);

  $added = true;
  }

}

//Add NEW Tag form
$added_new = false;
if (isset($_POST["add_new_tag"])) {

  $current_id = filter_input(INPUT_POST, 'add_new_current_id', FILTER_SANITIZE_NUMBER_INT);
  $tag =filter_input(INPUT_POST, 'add_new', FILTER_SANITIZE_STRING);

  $need=array();
  foreach($tags as $t){
    array_push($need, $t['name']);
  }
  if (in_array($tag, array_values($need)) == FALSE) {

    $sql = "INSERT INTO tags(name) VALUES (:tag_name)";
      $params = array(
        ':tag_name' => $tag,
      );
    exec_sql_query($db, $sql, $params);

    $tag_id = (int) $db->lastInsertId("id");

    $sql = "INSERT INTO image_tags (image_id, tag_id) VALUES ( :image_id, :tag_id)";
    $params = array(
    ':image_id' => $current_id,
    ':tag_id' => $tag_id
    );
    exec_sql_query($db, $sql, $params);
    $added_new = true;
  }
  $params=array();
  $tags = exec_sql_query($db,"SELECT tags.name FROM tags", $params) -> fetchAll();

}

//Remove Tag Form
$removed = false;
if (isset($_POST["remove_tag"])) {

  $current_id = filter_input(INPUT_POST, 'remove_current_id', FILTER_SANITIZE_NUMBER_INT);
  $tag =filter_input(INPUT_POST, 'tagToRemove', FILTER_SANITIZE_STRING);

  $need=array();
  foreach($tags as $t){
    array_push($need, $t['name']);
  }
  if (in_array($tag, array_values($need)) == TRUE) {

    $sql = "SELECT tags.id FROM tags WHERE tags.name = :tag_name";
    $params = array(
      ':tag_name' => $tag,
    );
    $result = exec_sql_query($db, $sql, $params) -> fetchAll();foreach ($result as $i){
      $tag_id = $i['id'];
    }

    $sql = "DELETE FROM image_tags WHERE image_tags.image_id= :image_id AND image_tags.tag_id= :tag_id";
    $params = array(
    ':image_id' => $current_id,
    ':tag_id' => $tag_id
    );
    exec_sql_query($db, $sql, $params);
    $removed=true;
  }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" type="text/css" href="styles/site.css" media="all" />
  <title>Photo Gallery</title>
</head>

<body>
  <header>
    <div class="head_div">
      <a href=https://www.youtube.com/user/cornellemotion>
      <img src="images/logo.png " alt="Logo" /></a>
      <!-- Source: https://www.youtube.com/user/cornellemotion-->
      <p class='head'> <cite> <a href = "https://www.youtube.com/user/cornellemotion"> Source </a></cite> </p>
      <p class='head'>by Cornell E.Motion</p>
    </div>
    <h1>Photo Gallery</h1>
    <div class="top">
      <section class="section1">
        <!-- Search -->
        <form id="searchForm" action="index.php" method="get" novalidate>
        <select class="type" name="type">
            <option class = "search_option" value="All">All Photos</option>
          <?php foreach ($tags as $field_name) { ?>
            <option class = "search_option" value="<?php echo $field_name['name']; ?>"><?php echo $field_name['name']; ?></option>
          <?php } ?>
        </select>
        <button class = "search_button" type="submit"> Search</button>
        </form>
      </section>
      <section class="section1">
      <form id="toUploadform" method = "get" action="index.php">
        <button class="toUpload" name = "toUpload" value='yes' >Upload Image </button>
       </form>
      </section>
    </div>
  </header>
  <main>
    <?php
    if(($_GET['toUpload'])){
    ?>
    <form class="uploadFile" id="uploadFile" method = "post" enctype="multipart/form-data" action="index.php">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
        <div class=form-div>
          <label for="upload_file">Upload File:</label>
          <input id="upload_file" type="file" name="upload_file">
        </div>

        <div class=form-div>
          <label for="upload_title">File Name:</label>
          <input id="upload_title" type="text" name="upload_title">
        </div>

        <div class=form-div>
          <label for="upload_uploader">File Uploader:</label>
          <input id="upload_uploader" type="text" name="upload_uploader">
        </div>

        <div class=form-div>
          <label for="desc">Description:</label>
          <textarea id="desc" name="description" cols="40" rows="5"></textarea>
        </div>

        <div class=form-div>
          <label for="upload_tags">Tags:</label>
          <div class="div_check">
          <?php foreach ($tags as $a_tag) { ?>
            <input type="checkbox" id = "<?php echo $a_tag['name']; ?>" name = "upload_tags_have[]" value = "<?php echo $a_tag['name']; ?>">
            <label class="tag_label" for="<?php echo $a_tag['name']; ?>"><?php echo $a_tag['name']; ?></label>

          <?php
          } ?>
          </div>
        </div>
        <div class="submit_button">
          <button class="submit_upload" name="submit_upload" type="submit">Upload Image</button>
        </div>
      </form>
  <?php
    }
  if($deleted){
    echo "Image has been deleted";
  } elseif($added){
    echo "Tag has been added";
  } elseif($removed){
    echo "Tag has been removed";
  }elseif($added_new){
    echo "NEW tag has been added";
  }
  if (isset($_GET["image_single_id"])){
    $single_image = TRUE;

    $single_image_id = $_GET["image_single_id"];
    $last_image = last_image($single_image_id, $db);
    $next_image = next_image($single_image_id, $db);

    ?>
    <section class = single_image_big_section>
      <div class="small_section">
        <button class="prev_button"><a href="index.php?<?php echo http_build_query(array('image_single_id' => strtolower($last_image))); ?>"><</a></button>
      </div>

    <?php


    $sql = "SELECT * FROM images WHERE images.id = :single_image_id;";
    $params = array(
      ':single_image_id' => $single_image_id
    );
    $result = exec_sql_query($db, $sql, $params) -> fetchAll();
    foreach ($result as $i) {
      ?><div class="single_image"><?php
      gallery_single($i);
      ?>
      </div>
      <div class="info">
        <div class="menu">
          <button class="menu_button">Options</button>
          <ul class="menu_content">
            <li><form id="toAddTag" method = "get" action="index.php">
               <input type="hidden" name="image_single_id" value="<?php echo $single_image_id ?>" />
                <input type="hidden" name="add" value="true" />
                <button class="li_button" name="to_add_tag" value='yes'>Add tag</button>
               </form></li>
               <li><form id="toAddNewTag" method = "get" action="index.php">
               <input type="hidden" name="image_single_id" value="<?php echo $single_image_id ?>" />
                <input type="hidden" name="add" value="true" />
                <button class="li_button" name="to_add_new_tag" value='yes'>Add<strong> NEW </strong>Tag</button>
               </form></li>
            <li><form id="toRemoveTag" method = "get" action="index.php">
            <input type="hidden" name="image_single_id" value="<?php echo $single_image_id ?>" />
                <button class="li_button" name="to_remove_tag" value='yes'>Remove Tag</button>
               </form></li>
            <ll>
              <form id="toDeleteFile" method = "post" enctype="multipart/form-data" action="index.php">
                <input type="hidden" name="delete_current_id" value="<?php echo $single_image_id ?>" />
                <input type="hidden" name="delete" value="true" />

                <button class="li_button" name="delete_image" value='yes'>Delete Image</button>
               </form></li>
          </ul>

        <?php
          if (isset($_GET["to_add_tag"])){
        ?>
          <form id="addTag" method = "post" enctype="multipart/form-data" action="index.php">
            <input  type="hidden" name="add_current_id" value="<?php echo $single_image_id ?>" />
            <select class="seconddown" name="tagToAdd">
              <?php foreach ($tags as $field_name) { ?>
              <option value="<?php echo $field_name['name']; ?>"><?php echo $field_name['name']; ?></option>
              <?php } ?>
             </select>

            <button class="second_button" name="add_tag">Add tag!</button>
          </form>
        <?php
          }
          if (isset($_GET["to_add_new_tag"])){
            ?>
              <form id="addNewTag" method = "post" enctype="multipart/form-data" action="index.php">
                <input  type="hidden" name="add_new_current_id" value="<?php echo $single_image_id ?>" />
                <input  id ="add_new" type="text" class="secondtext" name="add_new" value="Type new tag here" />

                <button class="second_button" name="add_new_tag">Add <strong>NEW</strong> tag!</button>
              </form>
            <?php
          }
          if (isset($_GET["to_remove_tag"])){
            $single_image_id = $_GET["image_single_id"];
            ?>
              <form id="RemoveTag" method = "post" enctype="multipart/form-data" action="index.php">
                <input type="hidden" name="remove_current_id" value="<?php echo $single_image_id ?>" />
                <select class="seconddown"  name="tagToRemove">
                  <?php
                  $sql = "SELECT tags.name FROM image_tags LEFT OUTER JOIN tags ON image_tags.tag_id = tags.id WHERE image_id = :single_image_id";
                  $params = array(
                    ':single_image_id' => $single_image_id,
                );
                  $need_tags = exec_sql_query($db,$sql, $params) -> fetchAll();
                  foreach ($need_tags as $field_name) { ?>
                  <option value="<?php echo $field_name['name']; ?>"><?php echo $field_name['name']; ?></option>
                  <?php } ?>
                 </select>

                <button class="second_button" name="remove_tag">Remove tag!</button>
              </form>
        <?php
          }
        ?>

        </div>
        <div class="parse_info">
          <p><strong>Description: </strong><?php echo $i['description']?></p>
          <p><strong>Uploader: </strong><?php echo $i['uploader']?></p>
          <p><strong> Tags: </strong>
          <?php
          $sql = "SELECT tags.name FROM tags LEFT OUTER JOIN image_tags ON image_tags.tag_id = tags.id WHERE image_id = :image_tag;";
          $params = array(
            ':image_tag' => $i['id']
          );
          $result = exec_sql_query($db, $sql, $params) -> fetchAll();
          foreach($result as $k){
            echo $k['name']; ?>, <?php
          }
          ?>
          </p>
          <p><strong>Citation (class purposes): </strong><?php echo $i['citation']?></p>
        </div>
      </div>
      <?php
    }
    ?>
    <div class="small_section">
      <button class="prev_button"><a href="index.php?<?php echo http_build_query(array('image_single_id' => strtolower($next_image))); ?>">></a></button>
    </div>
  </section>
    <?php
  }

  if($single_image == FALSE){
    if ($do_search) {
        $sql = "SELECT DISTINCT images.id, images.name, images.file_ext FROM images LEFT OUTER JOIN image_tags ON images.id = image_tags.image_id LEFT OUTER JOIN tags ON image_tags.tag_id = tags.id WHERE tags.name = :search;";
        $params = array(
          ':search' => $search_field
        );
    } else {
      $sql = "SELECT * FROM images;";
      $params = array();
    }
    $result = exec_sql_query($db, $sql, $params);
    $images = $result->fetchAll();
     ?>
     <div class="gallery">
      <?php
      if (count($images) > 0) {
        foreach ($images as $i) {
          gallery($i);
        }
      } else { ?>
        <p>No images matched your search.</p>
      <?php } ?>
    </div>
     <?php
  }

  ?>



  </main>
</body>
</html>
