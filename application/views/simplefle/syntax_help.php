<p><?php echo lang('qso_simplefle_syntax_help_ln1') ?>:</p>
<p><?php echo lang('qso_simplefle_syntax_help_ln2') ?></p>
<p><?php echo lang('qso_simplefle_syntax_help_ln3') ?></p>
<p><?php echo lang('qso_simplefle_syntax_help_ln4') ?></p>
<pre>
    20m ssb
    2134 2m0sql
</pre>
<p><?php echo lang('qso_simplefle_syntax_help_ln5') ?><p>
<p><?php echo lang('qso_simplefle_syntax_help_ln6') ?></p>
<pre>
    20m ssb
    2134 2m0sql
    6 la8aja 47 46
</pre>
<p><?php echo lang('qso_simplefle_syntax_help_ln7') ?></p>
<p><?php echo lang('qso_simplefle_syntax_help_ln8') ?></p>
<pre>
    20m ssb
    2134 2m0sql
    6 la8aja 47 46
    date 2021-05-14
    40m 
    40 dj7nt
    day ++
    df3et
</pre>
<p><?php echo lang('qso_simplefle_syntax_help_ln9') ?></p>

<p><strong>Satellite QSOs:</strong></p>
<p>For satellite contacts, use the <code>sat</code> or <code>satellite</code> keyword, followed by the satellite name and mode. The system will automatically populate frequencies from the satellite database.</p>
<pre>
    sat
    ao-7 V/U
    1234 m0abc
    1236 g4xyz
    
    satellite
    fo-29 U/L
    1300 dl1abc 59 57
</pre>
<p>Supported satellite modes include: V/U, U/V, L/S, V, U, etc.</p>

<p><strong>Gridsquares:</strong></p>
<p>You can include gridsquares (locators) on the same line as the callsign:</p>
<pre>
    20m ssb
    1234 g0abi io91
    1244 g0iiq io92tn
    
    40m cw
    1300 dl1abc jn00 59 57
</pre>
<p>Gridsquares must have 4 characters (2 letters + 2 digits) optionally followed by 2 subsquare letters (e.g., IO91, IO92AB, JN00, JN00AB).</p>

<p><strong>VUCC Gridsquares (Multiple Grids):</strong></p>
<p>For stations on a gridsquare corner, you can enter multiple comma-separated gridsquares (used for VUCC awards):</p>
<pre>
    2m fm
    1534 k0abc io91,io92
    1542 w5xyz jn00,jn01
    
    70cm ssb
    1600 n0msl en40,en41,en50,en51 59 59
</pre>
<p>Multiple gridsquares are stored as VUCC grids (separated by commas, no spaces needed).</p>

<p><strong>Comments:</strong></p>
<p>You can add comments to any QSO by enclosing text in angle brackets. The comment will be saved in the QSO's comment field:</p>
<pre>
    20m ssb
    1234 dl5mo < worked from home station >
    1240 2m0sql < great signal >
    
    40m cw
    1300 g4xyz < QRP 5W >
</pre>
<p>Everything inside &lt; &gt; will be stored as a comment and won't interfere with callsign or other field parsing.</p>
