<?php
require 'connexion.php';
if($connectedId !=0):
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Paramètres</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
        <a href='admin.php'><img src="logo.png" alt="Logo de notre réseau social"/> </a>
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $connectedId?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $connectedId?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $connectedId?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $connectedId?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $connectedId?>">Mes abonnements</a></li>
                </ul>

            </nav>
        </header>
        <div id="wrapper" class='profile'>


            <aside>
                <img src="user<?php echo $userId ?>.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                   
                    <p>Sur cette page vous trouverez les informations de l'utilisatrice
                        n° <?php echo $userId ?></p>
                        <form action="login.php" method ="post">
                            <input type="hidden" name="connectedid" value="<?php echo $_SESSION['connected_id'] ?>" >

                            <button type='submit' name='deconnexion'>Se déconnecter</button>
                            </form>

                </section>
            </aside>
            <main>
                

                <?php
                
                $laQuestionEnSql = "
                    SELECT users.*, 
                    count(DISTINCT posts.id) as totalpost, 
                    count(DISTINCT given.post_id) as totalgiven, 
                    count(DISTINCT recieved.user_id) as totalrecieved 
                    FROM users 
                    LEFT JOIN posts ON posts.user_id=users.id 
                    LEFT JOIN likes as given ON given.user_id=users.id 
                    LEFT JOIN likes as recieved ON recieved.post_id=posts.id 
                    WHERE users.id = '$userId' 
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                $user = $lesInformations->fetch_assoc();

               
                ?>                
                <article class='parameters'>
                    <h3>Mes paramètres</h3>
                    <dl>
                        <dt>Pseudo</dt>
                        <dd><?php echo $user['alias']?></dd>
                        <dt>Email</dt>
                        <dd><?php echo $user['email']?></dd>
                        <dt>Nombre de message</dt>
                        <dd><?php echo $user['totalpost']?></dd>
                        <dt>Nombre de "J'aime" donnés </dt>
                        <dd><?php echo $user['totalgiven']?></dd>
                        <dt>Nombre de "J'aime" reçus</dt>
                        <dd><?php echo $user['totalrecieved']?></dd>
                    </dl>

                </article>
            </main>
        </div>
    </body>
</html>
<?php else : ?>
    <p>Vous n'êtes pas connecté, impossible de charger la page</p>
    <a href="login.php">Se connecter </a> 
    <?php endif; ?>
