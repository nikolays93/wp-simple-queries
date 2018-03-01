# wp-simple-query

определенный шаблон ищется так (если шаблон не найден, будет использоваться последующий):
если тип product сначала проверит:
- theme/woocommerce/content-product-query.php
- theme/woocommerce/content-product.php

а потом:
- theme/[custom-template|settings-template|template-parts|]/content-#тип-query.php
- theme/[custom-template|settings-template|template-parts|]/content-#тип.php
- theme/[custom-template|settings-template|template-parts|]/content-query.php
- theme/[custom-template|settings-template|template-parts|]/content.php

категории:
- theme/[custom-template|settings-template|template-parts|]/section-#таксаномия-query.php
- theme/[custom-template|settings-template|template-parts|]/section-#таксаномия.php
- theme/[custom-template|settings-template|template-parts|]/section-query.php
- theme/[custom-template|settings-template|template-parts|]/section.php