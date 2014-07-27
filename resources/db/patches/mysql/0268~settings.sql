INSERT IGNORE INTO settings(setting, name, value, hint) VALUES('timeoutpath', 'timeoutpath', '', 'The path to the timeout binary. This is used to limit the amount of time unrar/7zip/mediainfo can run. You can the time limit in the process additional section. You can leave this empty to disable this. Use forward slashes in windows c:/path/to/timeout.exe');
INSERT IGNORE INTO settings(setting, name, value, hint) VALUES('timeoutseconds', 'timeoutseconds', '0', 'How much time to wait for unrar/7zip/mediainfo before killing it, set to 0 to disable. We do not run ffmpeg or avconv with timeout as it freezes. 60 is a good value. Requires the GNU Timeout path to be set.');