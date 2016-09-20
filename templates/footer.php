  <hr>

  <footer class="footer">
      <p class="clearfix">
          &copy; <?= date('Y')?>, Antoine Giraud</p>
      </p>
  </footer>

</div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="<?= $RouteHelper->publicUrl ?>js/bootstrap.min.js"></script>

    <?php if (!empty($js_for_layout)): ?>
      <?php foreach ($js_for_layout as $v):?>
        <?php if (file_exists($RouteHelper->publicPath.'js/'.$v.'.js')){ ?>
          <script src="<?= $RouteHelper->publicUrl.'js/'.$v.'.js' ?>"></script>
        <?php }elseif(file_exists($RouteHelper->publicPath.'js/'.$v)){ ?>
          <script src="<?= $RouteHelper->publicUrl ?>js/<?= $v; ?>"></script>
        <?php }elseif(false !== strpos($v, '<script type="text/javascript">')){ ?>
            <?= $v ?>
        <?php }else{ ?>
          <script type="text/javascript">
            <?= $v ?>
          </script>
        <?php } ?>
      <?php endforeach ?>
    <?php endif ?>
  </body>
</html>