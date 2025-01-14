<?php
file_put_contents('/tmp/unhealthy', 'true');
echo "Making the container health-check fail!";
?>