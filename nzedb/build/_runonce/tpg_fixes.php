<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt in the base directory.  If
 * not, see:
 *
 * @link <http://www.gnu.org/licenses/>.
 * @author niel
 * @copyright 2014 nZEDb
 */
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'config.php';

use nzedb\db\Settings;

$pdo = new Settings();

if (!$pdo->getSetting('tablepergroup')) {
	exit("Tables per groups is not enabled, quitting!");
}

$groups = $pdo->queryDirect('SELECT id FROM groups WHERE active = 1 OR backfill = 1');

if ($groups === false) {
	echo "No active groups. Fix not needed.\n";
} else {
	$query1 = "ALTER TABLE %s CHANGE COLUMN groupid group_id INT (10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to groups'";
	$query2 = "ALTER TABLE binaries_%s ADD COLUMN currentparts INT UNSIGNED NOT NULL DEFAULT '0' AFTER totalparts";
	$query3 = "UPDATE binaries_%s b SET currentparts = (SELECT COUNT(*) FROM parts_%s p WHERE p.binaryid = b.id)";

	$query4[] = "ALTER TABLE parts_%s DROP INDEX ix_parts_messageid";
	$query4[] = "ALTER TABLE parts_%s DROP INDEX ix_parts_number";
	$query4[] = "ALTER TABLE parts_%s ADD COLUMN collection_id INT(11) UNSIGNED NOT NULL DEFAULT '0'";
	$query4[] = "ALTER TABLE parts_%s ADD INDEX ix_parts_collection_id(collection_id)";
	$query4[] = "DROP TRIGGER IF EXISTS delete_collections_%s";

	$query5 = "CREATE TRIGGER delete_collections_%s AFTER DELETE ON collections_%s FOR EACH ROW BEGIN DELETE FROM binaries_%s WHERE collectionid = OLD.id; DELETE FROM parts_%s WHERE collection_id = OLD.id; END";

	foreach ($groups as $group) {
		echo 'Fixing group ' . $group['id'] . PHP_EOL;
		$pdo->queryExec(sprintf($query1, 'collections_' . $group['id']), true);
		$pdo->queryExec(sprintf($query1, 'partrepair_' . $group['id']), true);
		$pdo->queryExec(sprintf($query2, $group['id']), true);
		$pdo->queryExec(sprintf($query3, $group['id'], $group['id']), true);
		foreach ($query4 as $singleQuery) {
			$pdo->queryExec(sprintf($singleQuery, $group['id']), true);
		}
		$pdo->queryExec(sprintf($query5, $group['id'], $group['id'], $group['id'], $group['id']), true);
		echo 'Finished fixing group ' . $group['id'] . PHP_EOL;
	}
	echo 'All done!' . PHP_EOL;
}