<?php
  $configs = include('../config.php');
  require_once('./main.php');
?>

<html>
  <head>
    <title><?php echo $configs['product']['name']; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Calibri:400,700,400italic,700italic" />

    <style>
      body {
        font-family: 'Calibri';
        background-color: #eeeeee;
      }

      .panel, footer {
        margin-top: 80px;
      }

      header > div {
        margin: 30px 0px 50px 0px;
      }


      header section {
        margin: 50px 0px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="panel panel-default">
            <div class="panel-body">
              <header>
                <div class="row">
                  <div class="col-xs-5">
                    <img src="<?php echo $configs['header']['app_logo'] ?>" class="img-responsive center-block" alt="">
                  </div>
                  <div class="col-xs-2">
                    <img src="https://i.imgur.com/m6KwvdB.png" class="img-responsive center-block" alt="">
                  </div>
                  <div class="col-xs-5">
                    <img src="<?php echo $configs['header']['appsumo_logo'] ?>" class="img-responsive center-block" alt="">
                  </div>
                </div>

                <h1><?php echo $configs['header']['title'] ?></h1>

                <section>
                  <?php echo $configs['header']['description'] ?>
                </section>
              </header>

              <form action="" method="POST">
                <?php foreach($configs["form"]["fields"] as $field): ?>
                  <div class="form-group">
                    <label for="<?php echo $field['name'] ?>"><?php echo $field['label'] ?></label><br>
                    <input class="form-control" type="<?php echo $field['text'] ?>" id="<?php echo $field['name'] ?>" name="f_<?php echo $field['name'] ?>" required="<?php echo $field['required'] ? 'true' : 'false' ?>"><br>
                  </div>
                <?php endforeach; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                <button type="submit" class="btn btn-lg btn-primary">
                  <?php echo $configs["form"]['submit_btn_label'] ?>
                </button>
              </form>

              <footer>
                <div class="well">
                  <p class="pull-right">
                    <a href="#">Back to top</a>
                  </p>
                  <p>&copy; <?php echo date('Y'); ?> <?php echo $configs['product']['company']; ?> &middot; <a href="<?php echo $configs['product']['privary_url']; ?>">Privacy</a> &middot; <a href="<?php echo $configs['product']['terms_url']; ?>">Terms</a></p>
                </div>
              </footer>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
  </body>
</html>