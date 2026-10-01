<div class="modal fade" id="versionDialogModal" tabindex="-1" aria-labelledby="versionDialogLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="versionDialogLabel"><?php echo $this->optionslib->get_option('version_dialog_header'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <style>
                    /* Global heading styles for version modal */
                    #versionDialogModal h4 {
                        margin-top: 1rem;
                        margin-bottom: 0.75rem;
                        font-weight: 600;
                        line-height: 1.3;
                    }
                    
                    .release-notes h2, .release-notes h3, .release-notes h4, .release-notes h5 {
                        margin-top: 1.25rem;
                        margin-bottom: 0.5rem;
                        font-weight: 600;
                        line-height: 1.3;
                    }
                    .release-notes h2 { font-size: 1.5rem; margin-top: 1.5rem; }
                    .release-notes h3 { font-size: 1.3rem; }
                    .release-notes h4 { font-size: 1.15rem; }
                    .release-notes h5 { font-size: 1rem; }
                    .release-notes ul, .release-notes ol {
                        margin: 0.75rem 0;
                        padding-left: 2rem;
                        line-height: 1.6;
                    }
                    .release-notes li {
                        margin: 0.3rem 0;
                    }
                    .release-notes p {
                        margin: 0.5rem 0;
                        line-height: 1.6;
                    }
                    .release-notes code {
                        background-color: #f4f4f4;
                        padding: 0.2rem 0.4rem;
                        border-radius: 3px;
                        font-family: 'Courier New', monospace;
                        font-size: 0.9em;
                        color: #d63384;
                    }
                    .release-notes pre {
                        background-color: #f4f4f4;
                        padding: 1rem;
                        border-radius: 5px;
                        overflow-x: auto;
                        margin: 1rem 0;
                    }
                    .release-notes pre code {
                        background-color: transparent;
                        padding: 0;
                        color: inherit;
                    }
                    .release-notes a {
                        color: #0d6efd;
                        text-decoration: none;
                    }
                    .release-notes a:hover {
                        text-decoration: underline;
                    }
                    .release-notes strong {
                        font-weight: 600;
                    }
                    .release-notes em {
                        font-style: italic;
                    }
                    .release-notes img {
                        max-width: 100%;
                        height: auto;
                        margin: 1rem 0;
                        border-radius: 5px;
                        display: block;
                    }
                </style>

                <script src="<?php echo base_url('assets/js/showdown.min.js'); ?>"></script>

                                <?php
                $versionDialogMode = isset($this->optionslib) ? $this->optionslib->get_option('version_dialog') : 'release_notes';
                if ($versionDialogMode == 'custom_text' || $versionDialogMode == 'both') {
                ?>
                    <div class="border-bottom border-top p-4 m-4">
                        <?php
                        $versionDialogText = isset($this->optionslib) ? $this->optionslib->get_option('version_dialog_text') : null;
                        if ($versionDialogText !== null) {
                            // Apply markdown conversion to custom text
                            $htmlContent = $versionDialogText;
                            
                            // Convert inline code (`) - protect inline code from other conversions
                            $htmlContent = preg_replace('/`([^`]+)`/', '<code>$1</code>', $htmlContent);
                            
                            // Convert markdown horizontal rules (---, ***, ___) - split by line for robustness
                            $lines = preg_split('/\r?\n/', $htmlContent);
                            $processedLines = array();
                            foreach ($lines as $line) {
                                $trimmed = trim($line);
                                // Check if line is ONLY HR markers (3+ of same character, optionally with spaces)
                                if (preg_match('/^[ \t]{0,3}([\*\-_])\1{2,}[ \t]*$/', $trimmed)) {
                                    $processedLines[] = '<hr />';
                                } else {
                                    $processedLines[] = $line;
                                }
                            }
                            $htmlContent = implode("\n", $processedLines);
                            
                            // Convert blockquotes (>) before lists
                            $htmlContent = preg_replace_callback(
                                '/((?:^>[ \t]?.+$(?:\r?\n)?)+)/m',
                                function($matches) {
                                    $blockquoteContent = $matches[1];
                                    $blockquoteContent = preg_replace('/^>[ \t]?/m', '', $blockquoteContent);
                                    return "\n<blockquote>\n" . $blockquoteContent . "</blockquote>\n";
                                },
                                $htmlContent
                            );
                            
                            // Convert strikethrough (~~text~~)
                            $htmlContent = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $htmlContent);
                            
                            // Convert bullet points to list items
                            $htmlContent = preg_replace_callback(
                                '/((?:^[ \t]*[\*\-\+] .+$(?:\r?\n)?)+)/m',
                                function($matches) {
                                    $listContent = $matches[1];
                                    // Handle task lists first
                                    $listContent = preg_replace_callback(
                                        '/^[ \t]*[\*\-\+] \[[ xX]\]/m',
                                        function($m) {
                                            $isChecked = strpos($m[0], '[x') !== false || strpos($m[0], '[X') !== false;
                                            return '<input type="checkbox"' . ($isChecked ? ' checked' : '') . ' disabled> ';
                                        },
                                        $listContent
                                    );
                                    $listContent = preg_replace('/^[ \t]*[\*\-\+] (.+?)[ \t]*$/m', '<li>$1</li>', $listContent);
                                    $listContent = preg_replace('/(<\/li>)\r?\n(?=<li>)/', '$1', $listContent);
                                    return "\n<ul>\n" . $listContent . "</ul>\n";
                                },
                                $htmlContent
                            );
                            
                            // Convert numbered lists
                            $htmlContent = preg_replace_callback(
                                '/((?:^[ \t]*\d+\. .+$(?:\r?\n)?)+)/m',
                                function($matches) {
                                    $listContent = $matches[1];
                                    $listContent = preg_replace('/^[ \t]*\d+\. (.+?)[ \t]*$/m', '<li>$1</li>', $listContent);
                                    $listContent = preg_replace('/(<\/li>)\r?\n(?=<li>)/', '$1', $listContent);
                                    return "\n<ol>\n" . $listContent . "</ol>\n";
                                },
                                $htmlContent
                            );
                            
                            // Convert headers
                            $htmlContent = preg_replace('/^#### (.+)$/m', '<h5>$1</h5>', $htmlContent);
                            $htmlContent = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $htmlContent);
                            $htmlContent = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $htmlContent);
                            $htmlContent = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $htmlContent);
                            
                            // Convert bold and italic
                            $htmlContent = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $htmlContent);
                            $htmlContent = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $htmlContent);
                            $htmlContent = preg_replace('/(?<![*_])\*([^*\n]+)\*(?![*_])/', '<em>$1</em>', $htmlContent);
                            $htmlContent = preg_replace('/(?<![*_])_([^_\n]+)_(?![*_])/', '<em>$1</em>', $htmlContent);
                            
                            // Convert images and links
                            $htmlContent = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="img-fluid" style="max-width: 100%; height: auto;" />', $htmlContent);
                            $htmlContent = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $htmlContent);
                            
                            // Convert GitHub usernames and URLs
                            $htmlContent = preg_replace('/(?<![\w\/])@([a-zA-Z0-9_-]+)(?![\w])/', '<a href="https://github.com/$1" target="_blank">@$1</a>', $htmlContent);
                            $htmlContent = preg_replace('/(?<!href=["|\']|">|>\s)(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank">$1</a>', $htmlContent);
                            
                            // Protect block elements from nl2br
                            $htmlContent = preg_replace('/(<\/(?:h[1-6]|ul|ol|li|pre|blockquote)>)\r?\n/', '$1<!--BLOCK-->', $htmlContent);
                            $htmlContent = preg_replace('/(<(?:ul|ol|pre|blockquote)>)\r?\n/', '$1<!--BLOCK-->', $htmlContent);
                            $htmlContent = preg_replace('/(<hr\s*\/?>\r?\n)/', '$1<!--BLOCK-->', $htmlContent);
                            
                            // Convert line breaks
                            $htmlContent = nl2br($htmlContent);
                            
                            // Remove block markers
                            $htmlContent = preg_replace('/<!--BLOCK--><br\s*\/?>/', '', $htmlContent);
                            $htmlContent = preg_replace('/<br\s*\/?><!--BLOCK-->/', '', $htmlContent);
                            $htmlContent = preg_replace('/<!--BLOCK-->/', '', $htmlContent);
                            
                            // Cleanup extra br tags around block elements
                            // Remove ALL consecutive br tags before block elements
                            $htmlContent = preg_replace('/(?:<br\s*\/?>\s*)+(<(?:h[1-6]|ul|ol|li|pre|code|hr|blockquote)[^>]*>)/i', '$1', $htmlContent);
                            $htmlContent = preg_replace('/(<\/(?:h[1-6]|ul|ol|li|pre|blockquote)>|<hr\s*\/?>\s*)\s*(?:<br\s*\/?>\s*)+/i', '$1', $htmlContent);
                            $htmlContent = preg_replace('/(<br\s*\/?>){3,}/', '<br><br>', $htmlContent);
                            
                            // Remove br tags inside list items
                            $htmlContent = preg_replace('/(<li>[^<]*)<br\s*\/?>(\s*<\/li>)/i', '$1$2', $htmlContent);
                            
                            echo $htmlContent;
                        } else {
                            echo 'No Version Dialog text set. Go to the Admin Menu and set one.';
                        }
                        ?>
                    </div>
                <?php
                }
                if ($versionDialogMode == 'release_notes' || $versionDialogMode == 'both' || $versionDialogMode == 'disabled') {
                ?>
                    <div>
                        <?php
                        $url = 'https://api.github.com/repos/magicbug/Cloudlog/releases';
                        $options = [
                            'http' => [
                                'header' => 'User-Agent: Cloudlog - Amateur Radio Logbook'
                            ]
                        ];
                        $context = stream_context_create($options);
                        $response = file_get_contents($url, false, $context);

                        if ($response !== false) {
                            $data = json_decode($response, true);

                            $current_version = $this->optionslib->get_option('version');
                            
                            if ($data !== null && !empty($data)) {
                                $firstRelease = null;
                                foreach ($data as $singledata) {
                                    if ($singledata['tag_name'] == $current_version) {
                                        $firstRelease = $singledata;
                                        break;
                                    }
                                }

                                if ($firstRelease !== null) {
                                    $releaseBody = isset($firstRelease['body']) ? $firstRelease['body'] : 'No release information available';

                                    $releaseName = isset($firstRelease['name']) ? $firstRelease['name'] : 'No version name information available';
                                    echo "<h4>v" . $releaseName . "</h4>";
                                    
                                    // Convert markdown to HTML using PHP
                                    $htmlContent = $releaseBody;
                                    
                                    // Convert code blocks (```) - do this first to protect code content
                                    $htmlContent = preg_replace('/```([a-z]*)\r?\n(.*?)\r?\n```/s', '<pre><code>$2</code></pre>', $htmlContent);
                                    
                                    // Convert inline code (`) - protect inline code from other conversions
                                    $htmlContent = preg_replace('/`([^`]+)`/', '<code>$1</code>', $htmlContent);
                                    
                                    // Convert markdown horizontal rules (---, ***, ___) - split by line for robustness
                                    $lines = preg_split('/\r?\n/', $htmlContent);
                                    $processedLines = array();
                                    foreach ($lines as $line) {
                                        $trimmed = trim($line);
                                        // Check if line is ONLY HR markers (3+ of same character, optionally with spaces)
                                        if (preg_match('/^[ \t]{0,3}([\*\-_])\1{2,}[ \t]*$/', $trimmed)) {
                                            $processedLines[] = '<hr />';
                                        } else {
                                            $processedLines[] = $line;
                                        }
                                    }
                                    $htmlContent = implode("\n", $processedLines);
                                    
                                    // Convert blockquotes (>) before lists to avoid interference
                                    $htmlContent = preg_replace_callback(
                                        '/((?:^>[ \t]?.+$(?:\r?\n)?)+)/m',
                                        function($matches) {
                                            $blockquoteContent = $matches[1];
                                            // Remove leading > from each line and trim
                                            $blockquoteContent = preg_replace('/^>[ \t]?/m', '', $blockquoteContent);
                                            // Wrap in <blockquote> tags
                                            return "\n<blockquote>\n" . $blockquoteContent . "</blockquote>\n";
                                        },
                                        $htmlContent
                                    );
                                    
                                    // Convert strikethrough (~~text~~)
                                    $htmlContent = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $htmlContent);
                                    
                                    // Convert bullet points to list items (must be done before bold/italic to avoid interference)
                                    $htmlContent = preg_replace_callback(
                                        '/((?:^[ \t]*[\*\-\+] .+$(?:\r?\n)?)+)/m',
                                        function($matches) {
                                            $listContent = $matches[1];
                                            // Handle task lists first: [ ] or [x] or [X]
                                            $listContent = preg_replace_callback(
                                                '/^[ \t]*[\*\-\+] \[[ xX]\]/m',
                                                function($m) {
                                                    $isChecked = strpos($m[0], '[x') !== false || strpos($m[0], '[X') !== false;
                                                    return '<input type="checkbox"' . ($isChecked ? ' checked' : '') . ' disabled> ';
                                                },
                                                $listContent
                                            );
                                            // Convert each bullet point to <li>, stripping trailing whitespace
                                            $listContent = preg_replace('/^[ \t]*[\*\-\+] (.+?)[ \t]*$/m', '<li>$1</li>', $listContent);
                                            // Remove newlines between list items to prevent nl2br from adding extra breaks
                                            $listContent = preg_replace('/(<\/li>)\r?\n(?=<li>)/', '$1', $listContent);
                                            // Wrap in <ul> tags
                                            return "\n<ul>\n" . $listContent . "</ul>\n";
                                        },
                                        $htmlContent
                                    );
                                    
                                    // Convert numbered lists
                                    $htmlContent = preg_replace_callback(
                                        '/((?:^[ \t]*\d+\. .+$(?:\r?\n)?)+)/m',
                                        function($matches) {
                                            $listContent = $matches[1];
                                            // Convert each numbered item to <li>, stripping trailing whitespace
                                            $listContent = preg_replace('/^[ \t]*\d+\. (.+?)[ \t]*$/m', '<li>$1</li>', $listContent);
                                            // Remove newlines between list items to prevent nl2br from adding extra breaks
                                            $listContent = preg_replace('/(<\/li>)\r?\n(?=<li>)/', '$1', $listContent);
                                            // Wrap in <ol> tags
                                            return "\n<ol>\n" . $listContent . "</ol>\n";
                                        },
                                        $htmlContent
                                    );
                                    
                                    // Convert headers (process smaller headers first, then larger)
                                    $htmlContent = preg_replace('/^#### (.+)$/m', '<h5>$1</h5>', $htmlContent);
                                    $htmlContent = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $htmlContent);
                                    $htmlContent = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $htmlContent);
                                    $htmlContent = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $htmlContent);
                                    
                                    // Convert bold text (**text** or __text__) - must be before italic
                                    $htmlContent = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $htmlContent);
                                    $htmlContent = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $htmlContent);
                                    
                                    // Convert italic text (*text* or _text_)
                                    $htmlContent = preg_replace('/(?<![*_])\*([^*\n]+)\*(?![*_])/', '<em>$1</em>', $htmlContent);
                                    $htmlContent = preg_replace('/(?<![*_])_([^_\n]+)_(?![*_])/', '<em>$1</em>', $htmlContent);
                                    
                                    // Convert markdown images ![alt](url) - must be before regular links
                                    $htmlContent = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="img-fluid" style="max-width: 100%; height: auto;" />', $htmlContent);
                                    
                                    // Convert markdown links [text](url)
                                    $htmlContent = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $htmlContent);
                                    
                                    // Convert GitHub usernames (@username) to profile links
                                    $htmlContent = preg_replace('/(?<![\w\/])@([a-zA-Z0-9_-]+)(?![\w])/', '<a href="https://github.com/$1" target="_blank">@$1</a>', $htmlContent);
                                    
                                    // Convert plain URLs to links (but avoid already linked URLs)
                                    $htmlContent = preg_replace('/(?<!href=["|\']|">|>\s)(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank">$1</a>', $htmlContent);
                                    
                                    // Replace newlines after block-level elements with a marker to prevent nl2br from converting them
                                    $htmlContent = preg_replace('/(<\/(?:h[1-6]|ul|ol|li|pre|blockquote)>)\r?\n/', '$1<!--BLOCK-->', $htmlContent);
                                    $htmlContent = preg_replace('/(<(?:ul|ol|pre|blockquote)>)\r?\n/', '$1<!--BLOCK-->', $htmlContent);
                                    $htmlContent = preg_replace('/(<hr\s*\/?>\r?\n)/', '$1<!--BLOCK-->', $htmlContent);
                                    
                                    // Convert line breaks to <br> tags
                                    $htmlContent = nl2br($htmlContent);
                                    
                                    // Remove the markers (and any br tags that might have been added near them)
                                    $htmlContent = preg_replace('/<!--BLOCK--><br\s*\/?>/', '', $htmlContent);
                                    $htmlContent = preg_replace('/<br\s*\/?><!--BLOCK-->/', '', $htmlContent);
                                    $htmlContent = preg_replace('/<!--BLOCK-->/', '', $htmlContent);
                                    
                                    // Additional cleanup: remove br tags immediately before and after block elements
                                    // Remove ALL consecutive br tags before block elements
                                    $htmlContent = preg_replace('/(?:<br\s*\/?>\s*)+(<(?:h[1-6]|ul|ol|li|pre|code|hr|blockquote)[^>]*>)/i', '$1', $htmlContent);
                                    $htmlContent = preg_replace('/(<\/(?:h[1-6]|ul|ol|li|pre|blockquote)>|<hr\s*\/?>\s*)\s*(?:<br\s*\/?>\s*)+/i', '$1', $htmlContent);
                                    $htmlContent = preg_replace('/(<br\s*\/?>){3,}/', '<br><br>', $htmlContent);
                                    
                                    // Remove br tags inside list items
                                    $htmlContent = preg_replace('/(<li>[^<]*)<br\s*\/?>(\s*<\/li>)/i', '$1$2', $htmlContent);
                                    
                                    echo "<div class='release-notes mt-3'>" . $htmlContent . "</div>";
                                } else {
                                    echo '<h4>v' . $current_version . '</h4>';
                                    echo '<p>No release information found for this version on GitHub.</p>';
                                }
                            } else {
                                echo 'Error decoding JSON data or received empty response.';
                            }
                        } else {
                            echo 'Error retrieving data from the GitHub API.';
                        }
                        ?>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="modal-footer">
                <?php
                if ($versionDialogMode !== 'disabled') {
                ?>
                    <button class="btn btn-secondary" onclick="dismissVersionDialog()" data-bs-dismiss="modal"><?php echo lang('options_version_dialog_dismiss'); ?></button>
                <?php
                }
                ?>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo lang('options_version_dialog_close'); ?></button>
            </div>
        </div>
    </div>
</div>