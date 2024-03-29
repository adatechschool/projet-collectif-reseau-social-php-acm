<?php
require 'connexion.php';
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
        <a href='admin.php'><img src="logo.png" alt="Logo de notre réseau social"/> </a>
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href=<?php if ($connectedId != 0) {echo "wall.php?user_id=" . $connectedId;} else {echo "login.php" ;} ?>>Mur</a>
                <a href="feed.php?user_id=<?php echo $connectedId?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href=<?php if ($connectedId !=0 ) { echo "#";} else {echo "login.php";} ?>>Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $connectedId?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $connectedId?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $connectedId?>">Mes abonnements</a></li>
                </ul>

            </nav>
        </header>
        <div id="wrapper">
            

            <aside>
                <p>
                <?php
                               
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                if(isset($userId)){
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                    $user = $lesInformations->fetch_assoc();
                }
                
                $enCoursDeTraitement = isset($_POST['message']);
                    if ($enCoursDeTraitement)
                    {
                        
                        $authorId = $connectedId;
                        $postContent = $_POST['message'];
                        $namePost = $user['alias'];

                        $postContent = $mysqli->real_escape_string($postContent);

                       
                        $lInstructionSql = "INSERT INTO posts (id, user_id, content, created)
                                VALUES (NULL, $authorId, '$postContent' , NOW())";
                        
                        
                        $ok = $mysqli->query($lInstructionSql);
                        if ( ! $ok)
                        {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else
                        {
                            echo "Message posté en tant que " , $namePost;
                        }

                        require 'hashtag.php';
                
                }
                $enCoursDeTraitement2 = isset($_POST['button']);
                if($enCoursDeTraitement2) {
                    $followed = $userId;
                    $following = $connectedId;
                    $nameFollowed = $user['alias'];
                    $lInstructionSql2 = "INSERT INTO followers (id, followed_user_id, following_user_id)
                        VALUES (NULL, $followed, $following)";
                
                
                    $ok = $mysqli->query($lInstructionSql2);
                    if (! $ok)
                    {
                        echo "Impossible de suivre cette personne." . $mysqli->error;                 
                    } else 
                    {
                        echo "Vous suivez bien " ,$nameFollowed;
                    } 
                }

                $enCoursDeTraitement3 = isset($_POST['like']);
                if($enCoursDeTraitement3) {
                    //var_dump($_POST['like']); die;
                    $liker1 = $connectedId;
                    $likedPost1 = $_POST['postId'];
                    
                    $lInstructionSql3 = "INSERT INTO likes (id, user_id, post_id)
                        VALUES (NULL, $liker1, $likedPost1)";
                        
                    $ok = $mysqli->query($lInstructionSql3);
                    if (! $ok)
                    {
                        echo "Impossible de liker ce post." . $mysqli->error;                 
                    } else 
                    {
                        echo "Post liké";
                    } 
                }
                $enCoursDeTraitement4 = isset($_POST['dislike']);
                if($enCoursDeTraitement4) {
                    $liker = $connectedId;
                    $likedPost = $_POST['postId'];

                    $deleteSql = " DELETE FROM likes WHERE user_id = $liker AND post_id = $likedPost";
                    $ok = $mysqli->query($deleteSql);
                    if (! $ok)
                    {
                        echo "Impossible de dé-liker ce post." . $mysqli->error;                 
                    } else 
                    {
                        echo "Post dé-liké";
                    } 

                }
                $ecoutePostReponse = isset($_POST['answer']);
                if ($ecoutePostReponse)  {
                    $parentId = $_POST['postId'];
                    $postContent = $_POST['reponse'];
                    $ordreSql = "INSERT INTO posts (user_id, content, created, parent_id)
                    VALUES ($connectedId, '$postContent', NOW(),$parentId)";

                    $ok = $mysqli->query($ordreSql);
                    if (!$ok)
                    {
                        echo "Impossible de répondre" . $mysqli->error;
                    } else 
                    {
                        echo "réponse envoyée!";
                    }
                    require 'hashtag.php';
                }
                ?> </p>

                <img src="user<?php echo $userId ?>.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <?php if ($connectedId == $userId): ?>
                       <p> Bonjour <?php echo $user['alias'] ?> </p>
                    <?php endif; ?> 
                    <p>Sur cette page vous trouverez tous les message de l'utilisatrice  <?php echo $user['alias']?>
                        
                    </p>
                    <p><?php if ($connectedId == $userId) :?>
                        <form action="wall.php?user_id=<?php echo  $connectedId?>" method="post">
                        <input type = 'hidden' name='postId' value = "<?php echo $post['id'] ?>">
                        <dl>
                        <dt><label for='message'>Post</label></dt>
                        <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                    <?php else:?>
                        <form action="wall.php?user_id=<?php echo  $userId?>" method="post">
                        
                        <button type='submit' name='button'>Suivre</button>
                    <?php endif; ?> </p>  
                    
                </section>
            </aside>
            <main>
                <?php
                
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, posts.id, users.alias as author_name, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist,
                    GROUP_CONCAT(DISTINCT tags.id ORDER BY tags.label) AS tagid
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC 
                    LIMIT 10
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                
                while ($post = $lesInformations->fetch_assoc())
                {

                    //echo "<pre>" . print_r($post, 1) . "</pre>";
                    ?>  
                    
                    

                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13' ><?php echo $post['created']?></time>
                        </h3>
                        <address><?php echo $post['author_name']?></address>
                        <div>
                            <p><?php
                            $splittedString = explode("\n", $post['content']);
                            //echo $splittedString;
                            foreach($splittedString as $ligne){
                                echo $ligne;
                                echo '<br>';
                            }?></p>
                            
                         
                            
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number']?></small>
                            <form action="wall.php?user_id=<?php echo $connectedId ?>" method ="post">
                                <input type = 'hidden' name='postId' value = "<?php echo $post['id'] ?>">
                                <button type='submit' name='like'>Aimer</button>
                            </form>
                            <form action="wall.php?user_id=<?php echo $connectedId ?>" method ="post">
                                <input type = 'hidden' name='postId' value = "<?php echo $post['id'] ?>">
                                <button type='submit' name='dislike'>Ne plus aimer</button>
                            </form>
                            <p>
                                <?php
                                $splittedTag = explode(",", $post['taglist']);
                                $splittedId = explode(",", $post['tagid']);
                        //    echo $post['taglist'];
                        //    echo $post['tagid'];
                        //    print_r($splittedTag);
                        //    print_r($splittedId);
                                for ($i = 0 ; $i<count($splittedId); $i ++ ):?>
                                    <a href ="tags.php?tag_id=<?php
                                    echo $splittedId[$i];
                                ?>">#<?php echo $splittedTag[$i] ?></a> 
                                <?php endfor;
                            ?> 
                            </p> 
                                </br>
                            <form action="wall.php?user_id=<?php echo $connectedId ?>" method ="post">
                                <input type = 'hidden' name='postId' value = "<?php echo $post['id'] ?>">  
                                <textarea name='reponse'></textarea>
                                <button type='submit' name='answer'>Répondre</button>
                            </form>     
                        </footer>
                    </article>
                <?php } ?>


            </main>
        </div>
    </body>
</html>
