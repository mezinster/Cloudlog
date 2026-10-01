<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo htmlspecialchars('Station Diary - ' . $callsign, ENT_QUOTES); ?></title>
		<link><?php echo site_url('station-diary/' . rawurlencode($callsign)); ?></link>
		<description><?php echo htmlspecialchars('Public station diary feed for ' . $callsign, ENT_QUOTES); ?></description>
		<language>en-us</language>
		<atom:link href="<?php echo htmlspecialchars($current_url, ENT_QUOTES); ?>" rel="self" type="application/rss+xml" />
		<?php foreach ($entries as $entry) { ?>
						<?php $entryPermalink = site_url('station-diary/' . rawurlencode($callsign) . '/entry/' . (int)$entry->id); ?>
						<?php
						// Process image shortcodes and clean up content
						$CI =& get_instance();
						$CI->load->model('note');
						$processed = $CI->note->process_image_shortcodes($entry->note, $entry->images ?? []);
						// Remove empty <p><br></p> tags and <br> tags between paragraph tags
						$cleanedNote = preg_replace('/<p><br\s*\/?><\/p>/i', '', $processed['content']);
						$cleanedNote = preg_replace('/<\/p>\s*<br\s*\/?>\s*<p>/i', '</p><p>', $cleanedNote);
						?>
			<item>
				<title><?php echo htmlspecialchars($entry->title, ENT_QUOTES); ?></title>
				<link><?php echo htmlspecialchars($entryPermalink, ENT_QUOTES); ?></link>
				<guid><?php echo htmlspecialchars($entryPermalink, ENT_QUOTES); ?></guid>
				<pubDate><?php echo date(DATE_RSS, strtotime($entry->created_at)); ?></pubDate>
				<description><![CDATA[<?php echo $cleanedNote; ?>]]></description>
			</item>
		<?php } ?>
	</channel>
</rss>
