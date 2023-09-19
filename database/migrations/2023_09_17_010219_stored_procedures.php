<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->down();
		DB::unprepared("
		CREATE DEFINER=`root`@`localhost` PROCEDURE `getTags`()
		BEGIN
			SELECT JSON_DETAILED(
				JSON_OBJECT(
					'timestamp', NOW(),
					'tagList', JSON_ARRAYAGG(
						JSON_OBJECT(
							'tagId', `id`,
							'tagName', `tag`
						)
					)
				)
			) AS `tags`
			FROM `product_tags`;
		END");
		DB::unprepared("
		CREATE DEFINER=`root`@`localhost` PROCEDURE `manageProducts`(IN `pid` INT, IN `pname` VARCHAR(255) CHARSET utf8mb4, IN `pcode` VARCHAR(255) CHARSET utf8mb4, IN `category` VARCHAR(255) CHARSET utf8mb4, IN `pprice` FLOAT, IN `prelease_date` DATETIME, IN `tags` VARCHAR(255) CHARSET utf8mb4, IN `action` VARCHAR(6) CHARSET utf8mb4)
		BEGIN
			IF `action` = 'get' THEN
				IF CHAR_LENGTH(`category`) > 0 THEN
					SELECT 
					JSON_ARRAYAGG(
						JSON_OBJECT(
							'productId', `atp`.`id`,
							'productName', `atp`.`name`,
							'productCode', `atp`.`code`,
							'productCategory', `atc`.`name`,
							'productPrice', `atp`.`price`,
							'productTags', (SELECT JSON_ARRAYAGG(`att`.`tag`)
											FROM `amdocs_test`.`product_tag_associations` `ata`                    
											INNER JOIN `amdocs_test`.`product_tags` `att`
											ON `att`.`id` = `ata`.`tag_id`  
											WHERE `ata`.`product_id` = `atp`.`id`
										   ),
							'productReleaseDate', `atp`.`release_date`
						)
					) AS `products`
					FROM `amdocs_test`.`products` `atp`
					INNER JOIN `amdocs_test`.`product_categories` `atc`
					ON `atp`.`category_id` = `atc`.`id`
					WHERE LOWER(`atc`.`name`) = LOWER(`category`) COLLATE utf8mb4_unicode_ci;
				ELSE
					SELECT 
					JSON_ARRAYAGG(
						JSON_OBJECT(
							'productId', `atp`.`id`,
							'productName', `atp`.`name`,
							'productCode', `atp`.`code`,
							'productCategory', `atc`.`name`,
							'productPrice', `atp`.`price`,
							'productTags', (SELECT JSON_ARRAYAGG(`att`.`tag`)
											FROM `amdocs_test`.`product_tag_associations` `ata`                    
											INNER JOIN `amdocs_test`.`product_tags` `att`
											ON `att`.`id` = `ata`.`tag_id`  
											WHERE `ata`.`product_id` = `atp`.`id`
										   ),
							'productReleaseDate', `atp`.`release_date`
						)
					) AS `products`
					FROM `amdocs_test`.`products` `atp`
					INNER JOIN `amdocs_test`.`product_categories` `atc`
					ON `atp`.`category_id` = `atc`.`id`;
				END IF;
			END IF;
			
			IF `action` = 'update' AND `pid` > 0 THEN
				
				SELECT `name`, `code`, `category_id`, `price`, `release_date` FROM `amdocs_test`.`products`
				WHERE `id` = `pid`
				INTO @pname, @pcode, @cid, @pprice, @prelease_date;
				
				IF CHAR_LENGTH(`category`) > 0 THEN
					SELECT `id` FROM `amdocs_test`.`product_categories` WHERE `name` = `category` COLLATE utf8mb4_unicode_ci INTO @cid;
					IF @cid IS NULL THEN
						INSERT INTO `amdocs_test`.`product_categories` (`tag`) VALUES (`category`);
						SELECT `id` FROM `amdocs_test`.`product_categories` WHERE `name` = `category` COLLATE utf8mb4_unicode_ci INTO @cid;
					END IF;	
				END IF;
				
				UPDATE `amdocs_test`.`products`
				SET `name` = IF(CHAR_LENGTH(`pname`) > 0, `pname`, @pname),
					`code` = IF(CHAR_LENGTH(`pcode`) > 0, `pcode`, @pcode),
					`category_id` = @cid,
					`price` = IF(`pprice` > 0, `pprice`, @pprice), 
					`release_date` = IF(CHAR_LENGTH(`prelease_date`) > 0, `prelease_date`, @prelease_date)
				WHERE `id` = `pid`;
				
				IF CHAR_LENGTH(`tags`) > 0 THEN
				
					SET @tags = `tags`;
					CREATE TEMPORARY TABLE `tmp_tags`(
						`id` INT,
						`tag` VARCHAR(255)
					);
					WHILE LOCATE(',', @tags) > 0 DO
						SELECT SUBSTRING(@tags, 1, LOCATE(',', @tags)) INTO @tag;
						SET @ttag = TRIM(TRAILING ',' FROM @tag);
						SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @ttag COLLATE utf8mb4_unicode_ci INTO @tid;
						IF @tid IS NOT NULL THEN
							INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @ttag);
						ELSE 
							INSERT INTO `amdocs_test`.`product_tags` (`tag`) VALUES (@ttag);
							SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @ttag COLLATE utf8mb4_unicode_ci INTO @tid;
							INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @ttag);
						END IF;
						SET @tags = REPLACE(@tags, @tag, '');
						SET @tid = NULL;			
					END WHILE;
					SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @tags COLLATE utf8mb4_unicode_ci INTO @tid;
					IF @tid IS NOT NULL THEN
						INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @tags);
					ELSE 
						INSERT INTO `amdocs_test`.`product_tags` (`tag`) VALUES (@tags);
						SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @tags COLLATE utf8mb4_unicode_ci INTO @tid;
						INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @tags);
					END IF;
				
					DELETE FROM `amdocs_test`.`product_tag_associations` WHERE `product_id` = `pid`;
					INSERT INTO `amdocs_test`.`product_tag_associations` (`product_id`, `tag_id`) 
					SELECT `pid`, `id` FROM `tmp_tags`;
					DROP TABLE `tmp_tags`;
					
				END IF;
				SELECT 
					JSON_OBJECT(
						'productId', `atp`.`id`,
						'productName', `atp`.`name`,
						'productCode', `atp`.`code`,
						'productCategory', `atc`.`name`,
						'productPrice', `atp`.`price`,
						'productTags', (SELECT JSON_ARRAYAGG(`att`.`tag`)
										FROM `amdocs_test`.`product_tag_associations` `ata`                    
										INNER JOIN `amdocs_test`.`product_tags` `att`
										ON `att`.`id` = `ata`.`tag_id`  
										WHERE `ata`.`product_id` = `atp`.`id`
									   ),
						'productReleaseDate', `atp`.`release_date`
					) AS `products`
				FROM `amdocs_test`.`products` `atp`
				INNER JOIN `amdocs_test`.`product_categories` `atc`
				ON `atp`.`category_id` = `atc`.`id`
				WHERE `atp`.`id` = `pid`;
			END IF;
			
			IF `action` = 'create' AND CHAR_LENGTH(`pname`) > 0 AND CHAR_LENGTH(`pcode`) > 0 AND CHAR_LENGTH(`category`) > 0 AND `pprice` > 0 AND CHAR_LENGTH(`prelease_date`) > 0 AND CHAR_LENGTH(`tags`) > 0 THEN
			
				SET @tags = `tags`;
				CREATE TEMPORARY TABLE `tmp_tags`(
					`id` INT,
					`tag` VARCHAR(255)
				);
				WHILE LOCATE(',', @tags) > 0 DO
					SELECT SUBSTRING(@tags, 1, LOCATE(',', @tags)) INTO @tag;
					SET @ttag = TRIM(TRAILING ',' FROM @tag);
					SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @ttag COLLATE utf8mb4_unicode_ci INTO @tid;
					IF @tid IS NOT NULL THEN
						INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @ttag);
					ELSE 
						INSERT INTO `amdocs_test`.`product_tags` (`tag`) VALUES (@ttag);
						SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @ttag COLLATE utf8mb4_unicode_ci INTO @tid;
						INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @ttag);
					END IF;
					SET @tags = REPLACE(@tags, @tag, '');
					SET @tid = NULL;			
				END WHILE;
				SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @tags COLLATE utf8mb4_unicode_ci INTO @tid;
				IF @tid IS NOT NULL THEN
					INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @tags);
				ELSE 
					INSERT INTO `amdocs_test`.`product_tags` (`tag`) VALUES (@tags);
					SELECT `id` FROM `amdocs_test`.`product_tags` WHERE `tag` = @tags COLLATE utf8mb4_unicode_ci INTO @tid;
					INSERT INTO `tmp_tags` (`id`, `tag`) VALUES (@tid, @tags);
				END IF;
				
				SELECT `id` FROM `amdocs_test`.`product_categories` WHERE `name` = `category` COLLATE utf8mb4_unicode_ci INTO @cid;
				IF @cid IS NULL THEN
					INSERT INTO `amdocs_test`.`product_categories` (`tag`) VALUES (`category`);
					SELECT `id` FROM `amdocs_test`.`product_categories` WHERE `name` = `category` COLLATE utf8mb4_unicode_ci INTO @cid;
				END IF;	
						
				INSERT INTO `amdocs_test`.`products` (`name`, `code`, `category_id`, `price`, `release_date`) VALUES (`pname`, `pcode`, @cid, `pprice`, `prelease_date`);
				SELECT `id` FROM `amdocs_test`.`products` WHERE `name` = `pname` COLLATE utf8mb4_unicode_ci AND `category_id` = @cid INTO @pid;
				
				INSERT INTO `amdocs_test`.`product_tag_associations` (`product_id`, `tag_id`) 
				SELECT @pid, `id` FROM `tmp_tags`;
				DROP TABLE `tmp_tags`;

				SELECT 
					JSON_OBJECT(
						'productId', `atp`.`id`,
						'productName', `atp`.`name`,
						'productCode', `atp`.`code`,
						'productCategory', `atc`.`name`,
						'productPrice', `atp`.`price`,
						'productTags', (SELECT JSON_ARRAYAGG(`att`.`tag`)
										FROM `amdocs_test`.`product_tag_associations` `ata`                    
										INNER JOIN `amdocs_test`.`product_tags` `att`
										ON `att`.`id` = `ata`.`tag_id`  
										WHERE `ata`.`product_id` = `atp`.`id`
									   ),
						'productReleaseDate', `atp`.`release_date`
					) AS `products`
				FROM `amdocs_test`.`products` `atp`
				INNER JOIN `amdocs_test`.`product_categories` `atc`
				ON `atp`.`category_id` = `atc`.`id`
				WHERE `atp`.`id` = @pid;
				
			END IF;
		END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `amdocs_test`.`getTags`');
		DB::unprepared('DROP PROCEDURE IF EXISTS `amdocs_test`.`manageProducts`');
    }
};
