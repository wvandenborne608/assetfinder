 #!/bin/bash  
mysqldump -u root -p'***' assetfinder | ssh ***@***.***.***.*** mysql -u root -p'***' assetfinder
