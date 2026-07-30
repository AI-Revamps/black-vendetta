URL: [home] [clear cookies]

Options: Encode URL Encode Page Allow Cookies
PASTEBIN  |  #1 paste tool since 2002

    create new paste
    tools
    api
    archive
    real-time
    faq

PASTEBIN
create new paste     trending pastes

    sign up
    login
    my alerts
    my settings
    my profile

Public Pastes

    Untitled0 sec ago
    Untitled0 sec ago
    Untitled1 sec ago
    Untitled5 sec ago
    Untitled5 sec ago
    Untitled5 sec ago
    Untitled9 sec ago
    Untitled10 sec ago

Guest
Untitled
By: a guest on Nov 26th, 2011  |  syntax: SQL  |  size: 28.54 KB  |  hits: 97  |  expires: Never
download  |  raw  |  embed  |  report abuse

    SQL:
     
    #
    # Tabel structuur voor tabel `autorace`
    #
     
    CREATE TABLE `autorace` (
      `login` VARCHAR(16) NOT NULL DEFAULT \'\',
     `enemy` varchar(16) NOT NULL default \'\',
     `id1` varchar(5) NOT NULL default \'\',
     `id2` varchar(5) NOT NULL default \'\',
     `ready1` char(1) NOT NULL default \'\',
     `ready2` char(1) NOT NULL default \'\',
     `stad` varchar(25) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `autorace`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `backup-users`
    #
     
    CREATE TABLE `backup-users` (
     `id` int(4) NOT NULL auto_increment,
     `login` varchar(16) NOT NULL default \'\',
     `start` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `online` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `pass` varchar(50) NOT NULL default \'\',
     `email` varchar(255) NOT NULL default \'\',
     `ip` varchar(255) NOT NULL default \'\',
     `stad` varchar(100) NOT NULL default \'Brussel\',
     `geslacht` varchar(20) NOT NULL default \'\',
     `activated` char(1) NOT NULL default \'0\',
     `safe` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `health` int(3) NOT NULL default \'100\',
     `xp` int(6) NOT NULL default \'0\',
     `ac` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `crime` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `bc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `pc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `play` int(255) NOT NULL default \'0\',
     `zak` int(10) NOT NULL default \'1000\',
     `bank` int(10) NOT NULL default \'0\',
     `banktime` int(15) NOT NULL default \'0\',
     `respect` int(10) NOT NULL default \'0\',
     `se` decimal(4,1) NOT NULL default \'0.0\',
     `famillie` varchar(20) NOT NULL default \'\',
     `famrang` char(1) NOT NULL default \'0\',
     `famcapo` varchar(25) NOT NULL default \'\',
     `trans` char(2) NOT NULL default \'0\',
     `kogels` int(10) NOT NULL default \'0\',
     `wapon` char(2) NOT NULL default \'0\',
     `guard` int(2) NOT NULL default \'0\',
     `defence` int(2) NOT NULL default \'0\',
     `level` varchar(4) NOT NULL default \'1\',
     `rp` int(3) NOT NULL default \'0\',
     `kc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `status` varchar(10) NOT NULL default \'levend\',
     `drugs` char(2) NOT NULL default \'0\',
     `drank` char(2) NOT NULL default \'0\',
     `drugst` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `drankt` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `energie` decimal(3,1) NOT NULL default \'99.9\',
     `slaap` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `sl` char(1) NOT NULL default \'0\',
     `vet` char(3) NOT NULL default \'0\',
     `Brussel` char(1) NOT NULL default \'0\',
     `transport` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `Leuven` char(1) NOT NULL default \'0\',
     `Gent` char(1) NOT NULL default \'0\',
     `Brugge` char(1) NOT NULL default \'0\',
     `Hasselt` char(1) NOT NULL default \'0\',
     `Antwerpen` char(1) NOT NULL default \'0\',
     `Amsterdam` char(1) NOT NULL default \'0\',
     `Enschede` char(1) NOT NULL default \'\',
     `pic` varchar(255) NOT NULL default \'\',
     `info` text NOT NULL,
     `bo` int(255) NOT NULL default \'0\',
     `nrofcrime` int(10) NOT NULL default \'0\',
     `nrofcar` int(10) NOT NULL default \'0\',
     `nrofroute` int(10) NOT NULL default \'0\',
     `nrofoc` int(10) NOT NULL default \'0\',
     `nrofrace` int(10) NOT NULL default \'0\',
     `nrofkill` int(10) NOT NULL default \'0\',
     `testament` varchar(16) NOT NULL default \'\',
     `huwelijk` varchar(16) NOT NULL default \'\',
     `bf` int(10) NOT NULL default \'0\',
     `lang` char(2) NOT NULL default \'nl\',
     `paid` char(1) NOT NULL default \'0\',
     `ah` int(1) NOT NULL default \'0\',
     `dh` int(1) NOT NULL default \'0\',
     `gstart` int(2) NOT NULL default \'0\',
     PRIMARY KEY  (`id`),
     UNIQUE KEY `login` (`login`),
     KEY `id` (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `backup-users`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `bans`
    #
     
    CREATE TABLE `bans` (
     `ip` varchar(255) NOT NULL default \'\',
     `reden` varchar(255) NOT NULL default \'\',
     `door` varchar(16) NOT NULL default \'\',
     `login` varchar(16) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `bans`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `blackjack`
    #
     
    CREATE TABLE `blackjack` (
     `id` int(4) NOT NULL auto_increment,
     `login` varchar(16) NOT NULL default \'\',
     `inzet` int(14) NOT NULL default \'0\',
     `kaart` int(2) NOT NULL default \'0\',
     `kaartpic` longtext NOT NULL,
     `aas` int(2) NOT NULL default \'0\',
     `dealer` int(2) NOT NULL default \'0\',
     `dealerpic` varchar(255) NOT NULL default \'\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `blackjack`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `cars`
    #
     
    CREATE TABLE `cars` (
     `naam` varchar(16) NOT NULL default \'\',
     `url` varchar(255) NOT NULL default \'\',
     `waarde` varchar(6) NOT NULL default \'\',
     `auto` varchar(255) NOT NULL default \'\',
     FULLTEXT KEY `naam` (`naam`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `cars`
    #
     
    INSERT INTO `cars` VALUES (\'45000\', \'images/autos/buick_cabrio.jpg\', \'45000\', \'Buick Cabrio\');
    INSERT INTO `cars` VALUES (\'40000\', \'images/autos/aston_martin_db3s.jpg\', \'40000\', \'Aston Martin DB3S\');
    INSERT INTO `cars` VALUES (\'30000\', \'images/autos/ford_fairlane.jpg\', \'30000\', \'Ford Fairlane\');
    INSERT INTO `cars` VALUES (\'10000\', \'images/autos/pontiac.jpg\', \'10000\', \'Pontiac\');
    INSERT INTO `cars` VALUES (\'25000\', \'images/autos/dodge_kingsway.jpg\', \'25000\', \'Dodge Kingsway\');
    INSERT INTO `cars` VALUES (\'60000\', \'images/autos/chrysler_converible.jpg\', \'60000\', \'Chrysler Converible\');
    INSERT INTO `cars` VALUES (\'65000\', \'images/autos/pontiac_bonneville.jpg\', \'65000\', \'Pontiac Bonneville\');
    INSERT INTO `cars` VALUES (\'80000\', \'images/autos/dodge_dart.jpg\', \'80000\', \'Dodge Dart\');
    INSERT INTO `cars` VALUES (\'95000\', \'images/autos/mercury_cougar.jpg\', \'95000\', \'Mercury Cougar\');
    INSERT INTO `cars` VALUES (\'125000\', \'images/autos/chevy_camaro_ss.jpg\', \'125000\', \'Chevy Camaro SS\');
    INSERT INTO `cars` VALUES (\'150000\', \'images/autos/ford_mustang.jpg\', \'150000\', \'Ford Mustang\');
    INSERT INTO `cars` VALUES (\'15000\', \'images/autos/caddillac_sedan.jpg\', \'15000\', \'Caddillac Sedan\');
    INSERT INTO `cars` VALUES (\'175000\', \'images/autos/alfa_romeo _fnm_2000_jk.jpg\', \'175000\', \'Alfa Romeo FNM 2000 JK\');
    INSERT INTO `cars` VALUES (\'200000\', \'images/autos/porsche_356b_cabriolet.jpg\', \'200000\', \'Porsche 356B Cabriolet\');
    INSERT INTO `cars` VALUES (\'400000\', \'images/autos/streamliner.jpg\', \'400000\', \'Mercedes W124 Avus Streamling\');
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `casino`
    #
     
    CREATE TABLE `casino` (
     `id` int(4) NOT NULL auto_increment,
     `spel` varchar(255) NOT NULL default \'\',
     `owner` varchar(16) NOT NULL default \'\',
     `stad` varchar(20) NOT NULL default \'\',
     `winst` varchar(7) NOT NULL default \'0\',
     `inzet` varchar(7) NOT NULL default \'1000\',
     `status` int(1) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `casino`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `cron`
    #
     
    CREATE TABLE `cron` (
     `name` varchar(16) NOT NULL default \'\',
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `cron`
    #
     
    INSERT INTO `cron` VALUES (\'kogels\', \'0000-00-00 00:00:00\');
    INSERT INTO `cron` VALUES (\'week\', \'0000-00-00 00:00:00\');
    INSERT INTO `cron` VALUES (\'day\', \'0000-00-00 00:00:00\');
    INSERT INTO `cron` VALUES (\'loterij\', \'0000-00-00 00:00:00\');
    INSERT INTO `cron` VALUES (\'uur\', \'0000-00-00 00:00:00\');
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `detectives`
    #
     
    CREATE TABLE `detectives` (
     `van` varchar(16) NOT NULL default \'\',
     `naar` varchar(16) NOT NULL default \'\',
     `stad` varchar(30) NOT NULL default \'\',
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `id` int(4) NOT NULL auto_increment,
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `detectives`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `donate`
    #
     
    CREATE TABLE `donate` (
     `id` int(10) NOT NULL auto_increment,
     `door` varchar(25) NOT NULL default \'\',
     `code` varchar(255) NOT NULL default \'\',
     `status` int(1) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `donate`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `famillie`
    #
     
    CREATE TABLE `famillie` (
     `name` varchar(16) NOT NULL default \'\',
     `info` text NOT NULL,
     `crusher` int(15) NOT NULL default \'0\',
     `aantal` int(4) NOT NULL default \'0\',
     `stad` varchar(20) NOT NULL default \'\',
     `pic` varchar(255) NOT NULL default \'\',
     `bank` int(7) NOT NULL default \'0\',
     `grond` int(5) NOT NULL default \'50\',
     `rang2` int(20) NOT NULL default \'0\',
     `rang3` int(20) NOT NULL default \'0\',
     `rang4` int(20) NOT NULL default \'0\',
     `rang5` int(20) NOT NULL default \'0\',
     `rang6` int(20) NOT NULL default \'0\',
     `rang7` int(20) NOT NULL default \'0\',
     `rang8` int(20) NOT NULL default \'0\',
     `rang9` int(20) NOT NULL default \'0\',
     `rang10` int(20) NOT NULL default \'0\',
     `rang11` int(20) NOT NULL default \'0\',
     `rang12` int(20) NOT NULL default \'0\',
     `rang13` int(20) NOT NULL default \'0\',
     `rang14` int(20) NOT NULL default \'0\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `famillie`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `forum_reacties`
    #
     
    CREATE TABLE `forum_reacties` (
     `id` int(10) unsigned NOT NULL auto_increment,
     `topic_id` int(10) NOT NULL default \'0\',
     `user` varchar(30) NOT NULL default \'\',
     `subject` varchar(50) NOT NULL default \'\',
     `message` text NOT NULL,
     `date` datetime NOT NULL default \'0000-00-00 00:00:00\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `forum_reacties`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `forum_topics`
    #
     
    CREATE TABLE `forum_topics` (
     `id` int(10) unsigned NOT NULL auto_increment,
     `type` varchar(255) NOT NULL default \'\',
     `user` varchar(30) NOT NULL default \'\',
     `subject` varchar(50) NOT NULL default \'\',
     `message` text NOT NULL,
     `date` datetime NOT NULL default \'0000-00-00 00:00:00\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `forum_topics`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `friends`
    #
     
    CREATE TABLE `friends` (
     `login` varchar(16) NOT NULL default \'\',
     `friend` varchar(16) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `friends`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `garage`
    #
     
    CREATE TABLE `garage` (
     `id` int(4) NOT NULL auto_increment,
     `login` varchar(16) NOT NULL default \'\',
     `naam` varchar(16) NOT NULL default \'\',
     `waarde` int(6) NOT NULL default \'0\',
     `damage` char(3) NOT NULL default \'\',
     `stad` varchar(16) NOT NULL default \'\',
     `safe` char(1) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `garage`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `hitlist`
    #
     
    CREATE TABLE `hitlist` (
     `login` varchar(16) NOT NULL default \'\',
     `suspect` varchar(16) NOT NULL default \'\',
     `prijs` int(9) NOT NULL default \'0\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `hitlist`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `invite`
    #
     
    CREATE TABLE `invite` (
     `login` varchar(16) NOT NULL default \'\',
     `famillie` varchar(16) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `invite`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `iplog`
    #
     
    CREATE TABLE `iplog` (
     `login` varchar(16) NOT NULL default \'\',
     `ip` varchar(255) NOT NULL default \'\',
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `allo` char(1) NOT NULL default \'0\',
     `status` varchar(255) NOT NULL default \'levend\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `iplog`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `items`
    #
     
    CREATE TABLE `items` (
     `id` int(4) NOT NULL auto_increment,
     `nr` char(1) NOT NULL default \'\',
     `type` varchar(5) NOT NULL default \'\',
     `naam` varchar(16) NOT NULL default \'\',
     `aprijs` int(10) NOT NULL default \'0\',
     `vprijs` int(10) NOT NULL default \'0\',
     `effect` decimal(10,2) NOT NULL default \'0.00\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM AUTO_INCREMENT=18 ;
     
    #
    # Gegevens worden uitgevoerd voor tabel `items`
    #
     
    INSERT INTO `items` VALUES (1, \'1\', \'att\', \'Uzi\', 25000, 20000, \'3.00\');
    INSERT INTO `items` VALUES (2, \'2\', \'att\', \'M16\', 50000, 40000, \'2.00\');
    INSERT INTO `items` VALUES (3, \'1\', \'trans\', \'Treinabbonnement\', 250000, 200000, \'3600.00\');
    INSERT INTO `items` VALUES (4, \'3\', \'trans\', \'Limousine\', 1000000, 750000, \'1800.00\');
    INSERT INTO `items` VALUES (7, \'4\', \'att\', \'Sniper Rifle\', 100000, 80000, \'1.50\');
    INSERT INTO `items` VALUES (8, \'2\', \'trans\', \'Taxi\', 750000, 600000, \'2400.00\');
    INSERT INTO `items` VALUES (9, \'3\', \'att\', \'9mm\', 10000, 8000, \'4.00\');
    INSERT INTO `items` VALUES (10, \'4\', \'trans\', \'Privé-Jet\', 1500000, 1200000, \'900.00\');
    INSERT INTO `items` VALUES (13, \'6\', \'att\', \'Tommy Gun\', 140000, 120000, \'2.00\');
    INSERT INTO `items` VALUES (14, \'5\', \'att\', \'Magnum Semi Auto\', 75000, 60000, \'1.99\');
    INSERT INTO `items` VALUES (15, \'0\', \'def\', \'Geen bescherming\', 0, 0, \'1.00\');
    INSERT INTO `items` VALUES (16, \'2\', \'def\', \'Kogelvrije vest\', 100000, 75000, \'4.00\');
    INSERT INTO `items` VALUES (17, \'1\', \'def\', \'Kogelvrij schild\', 30000, 20000, \'2.00\');
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `jail`
    #
     
    CREATE TABLE `jail` (
     `login` varchar(16) NOT NULL default \'\',
     `boete` varchar(6) NOT NULL default \'\',
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `stad` varchar(255) NOT NULL default \'\',
     `famillie` varchar(255) NOT NULL default \'\',
     `bo` int(1) NOT NULL default \'0\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `jail`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `kras`
    #
     
    CREATE TABLE `kras` (
     `login` varchar(16) NOT NULL default \'\',
     `aantal` int(1) NOT NULL default \'0\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `kras`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `log`
    #
     
    CREATE TABLE `log` (
     `code` varchar(255) NOT NULL default \'\',
     `wat` varchar(255) NOT NULL default \'\',
     `aantal` int(255) NOT NULL default \'0\',
     `van` varchar(16) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `log`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `logs`
    #
     
    CREATE TABLE `logs` (
     `time` datetime default NULL,
     `login` varchar(16) default NULL,
     `person` varchar(16) default NULL,
     `code` int(10) default NULL,
     `area` varchar(32) default NULL,
     `com` varchar(255) NOT NULL default \'Geen\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `logs`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `loterij`
    #
     
    CREATE TABLE `loterij` (
     `id` int(100) NOT NULL auto_increment,
     `login` varchar(255) NOT NULL,
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `loterij`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `messages`
    #
     
    CREATE TABLE `messages` (
     `id` int(4) NOT NULL auto_increment,
     `from` varchar(16) NOT NULL default \'\',
     `to` varchar(16) NOT NULL default \'\',
     `subject` varchar(50) NOT NULL default \'\',
     `message` text NOT NULL,
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `read` int(1) NOT NULL default \'0\',
     `inbox` int(1) NOT NULL default \'0\',
     `save` int(1) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `messages`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `mshop`
    #
     
    CREATE TABLE `mshop` (
     `id` int(10) NOT NULL auto_increment,
     `door` varchar(25) NOT NULL default \'\',
     `type` varchar(10) NOT NULL default \'\',
     `specs` varchar(255) NOT NULL default \'\',
     `bieder` varchar(25) NOT NULL default \'\',
     `aflooptijd` int(15) NOT NULL default \'0\',
     `specs2` varchar(255) NOT NULL default \'\',
     `specs3` varchar(255) NOT NULL default \'\',
     `bod` int(15) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `mshop`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `multiple`
    #
     
    CREATE TABLE `multiple` (
     `ip` varchar(255) NOT NULL default \'1\',
     `allo` char(1) NOT NULL default \'1\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `multiple`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `news`
    #
     
    CREATE TABLE `news` (
     `id` int(4) NOT NULL auto_increment,
     `title` varchar(100) NOT NULL default \'\',
     `text` text NOT NULL,
     `time` int(15) NOT NULL default \'0\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `news`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `oc`
    #
     
    CREATE TABLE `oc` (
     `id` int(100) NOT NULL auto_increment,
     `login` varchar(100) default NULL,
     `we` varchar(100) default NULL,
     `be` varchar(100) default NULL,
     `dr` varchar(100) default NULL,
     `ready1` int(1) NOT NULL default \'0\',
     `ready2` int(1) NOT NULL default \'0\',
     `ready3` int(1) NOT NULL default \'0\',
     `wapens` varchar(100) NOT NULL default \'0\',
     `kogels` int(100) NOT NULL default \'0\',
     `bommen` varchar(100) NOT NULL default \'0\',
     `aantal` int(100) NOT NULL default \'0\',
     `auto` varchar(100) NOT NULL default \'\',
     `klaar` int(1) NOT NULL default \'0\',
     `autoid` int(255) NOT NULL default \'0\',
     `damage` char(3) NOT NULL default \'\',
     `stad` varchar(255) NOT NULL default \'\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `oc`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `poll`
    #
     
    CREATE TABLE `poll` (
     `id` int(11) NOT NULL auto_increment,
     `vraag` varchar(200) NOT NULL default \'\',
     `actief` int(1) NOT NULL default \'0\',
     `datum` int(20) NOT NULL default \'0\',
     `keuze1` varchar(50) NOT NULL default \'\',
     `keuze2` varchar(50) NOT NULL default \'\',
     `keuze3` varchar(50) NOT NULL default \'\',
     `keuze4` varchar(50) NOT NULL default \'\',
     `keuze5` varchar(50) NOT NULL default \'\',
     `keuze6` varchar(50) NOT NULL default \'\',
     `keuze7` varchar(50) NOT NULL default \'\',
     `keuze8` varchar(50) NOT NULL default \'\',
     `keuze9` varchar(50) NOT NULL default \'\',
     `keuze10` varchar(50) NOT NULL default \'\',
     `antwoord1` int(11) NOT NULL default \'0\',
     `antwoord2` int(11) NOT NULL default \'0\',
     `antwoord3` int(11) NOT NULL default \'0\',
     `antwoord4` int(11) NOT NULL default \'0\',
     `antwoord5` int(11) NOT NULL default \'0\',
     `antwoord6` int(11) NOT NULL default \'0\',
     `antwoord7` int(11) NOT NULL default \'0\',
     `antwoord8` int(11) NOT NULL default \'0\',
     `antwoord9` int(11) NOT NULL default \'0\',
     `antwoord10` int(11) NOT NULL default \'0\',
     `gestemd` text NOT NULL,
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `poll`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `route66`
    #
     
    CREATE TABLE `route66` (
     `login` varchar(16) NOT NULL default \'\',
     `driver` varchar(16) NOT NULL default \'\',
     `stad` varchar(255) NOT NULL default \'\',
     `ready1` char(1) NOT NULL default \'\',
     `ready2` char(1) NOT NULL default \'\',
     `car` int(4) NOT NULL default \'0\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `route66`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `shame`
    #
     
    CREATE TABLE `shame` (
     `id` int(4) NOT NULL auto_increment,
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `cheater` varchar(16) NOT NULL default \'\',
     `person` varchar(16) NOT NULL default \'\',
     `com` varchar(255) NOT NULL default \'\',
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `shame`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `stad`
    #
     
    CREATE TABLE `stad` (
     `stad` varchar(255) NOT NULL default \'\',
     `kogels` int(4) NOT NULL default \'500\',
     `prijs` int(5) NOT NULL default \'1000\',
     `drugs` char(3) NOT NULL default \'50\',
     `drank` char(3) NOT NULL default \'50\',
     `drugsp` varchar(5) NOT NULL default \'2500\',
     `drankp` varchar(4) NOT NULL default \'2500\',
     `transp` varchar(5) NOT NULL default \'2000\',
     `grond` int(5) NOT NULL default \'1000\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `stad`
    #
     
    INSERT INTO `stad` VALUES (\'Brussel\', 100, 1273, \'32\', \'464\', \'14903\', \'3402\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Leuven\', 100, 1273, \'194\', \'196\', \'13734\', \'1093\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Gent\', 100, 1273, \'104\', \'17\', \'7049\', \'5630\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Hasselt\', 100, 1273, \'381\', \'132\', \'12659\', \'1312\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Antwerpen\', 100, 1273, \'33\', \'28\', \'6172\', \'4550\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Brugge\', 100, 1273, \'331\', \'6\', \'7190\', \'3668\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Amsterdam\', 100, 1273, \'194\', \'4\', \'12955\', \'3408\', \'2000\', 1000);
    INSERT INTO `stad` VALUES (\'Enschede\', 100, 1273, \'544\', \'0\', \'10080\', \'2803\', \'2000\', 1000);
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `temp`
    #
     
    CREATE TABLE `temp` (
     `id` int(16) NOT NULL auto_increment,
     `time` datetime default NULL,
     `login` varchar(16) default NULL,
     `IP` varchar(32) default NULL,
     `forwardedFor` varchar(32) NOT NULL default \'\',
     `code` int(10) unsigned NOT NULL default \'0\',
     `area` varchar(32) default NULL,
     PRIMARY KEY  (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `temp`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `trouwen`
    #
     
    CREATE TABLE `trouwen` (
     `login` varchar(16) NOT NULL default \'\',
     `partner` varchar(16) NOT NULL default \'\',
     `stad` varchar(255) NOT NULL default \'\',
     `ready1` char(1) NOT NULL default \'\',
     `ready2` char(1) NOT NULL default \'\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `trouwen`
    #
     
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `users`
    #
     
    CREATE TABLE `users` (
     `id` int(4) NOT NULL auto_increment,
     `login` varchar(16) NOT NULL default \'\',
     `start` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `online` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `pass` varchar(50) NOT NULL default \'\',
     `email` varchar(255) NOT NULL default \'\',
     `ip` varchar(255) NOT NULL default \'\',
     `stad` varchar(100) NOT NULL default \'Brussel\',
     `geslacht` varchar(20) NOT NULL default \'\',
     `activated` char(1) NOT NULL default \'0\',
     `safe` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `health` int(3) NOT NULL default \'100\',
     `xp` int(6) NOT NULL default \'0\',
     `ac` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `crime` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `bc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `pc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `play` int(255) NOT NULL default \'0\',
     `zak` int(10) NOT NULL default \'1000\',
     `bank` int(10) NOT NULL default \'0\',
     `banktime` int(15) NOT NULL default \'0\',
     `respect` int(10) NOT NULL default \'0\',
     `se` decimal(4,1) NOT NULL default \'0.0\',
     `famillie` varchar(20) NOT NULL default \'\',
     `famrang` char(1) NOT NULL default \'0\',
     `famcapo` varchar(25) NOT NULL default \'\',
     `trans` char(2) NOT NULL default \'0\',
     `kogels` int(10) NOT NULL default \'0\',
     `wapon` char(2) NOT NULL default \'0\',
     `guard` int(2) NOT NULL default \'0\',
     `defence` int(2) NOT NULL default \'0\',
     `level` varchar(4) NOT NULL default \'1\',
     `rp` int(3) NOT NULL default \'0\',
     `kc` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `status` varchar(10) NOT NULL default \'levend\',
     `drugs` char(2) NOT NULL default \'0\',
     `drank` char(2) NOT NULL default \'0\',
     `drugst` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `drankt` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `energie` decimal(3,1) NOT NULL default \'99.9\',
     `slaap` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `sl` char(1) NOT NULL default \'0\',
     `vet` char(3) NOT NULL default \'0\',
     `Brussel` char(1) NOT NULL default \'0\',
     `transport` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `Leuven` char(1) NOT NULL default \'0\',
     `Gent` char(1) NOT NULL default \'0\',
     `Brugge` char(1) NOT NULL default \'0\',
     `Hasselt` char(1) NOT NULL default \'0\',
     `Antwerpen` char(1) NOT NULL default \'0\',
     `Amsterdam` char(1) NOT NULL default \'0\',
     `Enschede` char(1) NOT NULL default \'\',
     `pic` varchar(255) NOT NULL default \'\',
     `info` text NOT NULL,
     `bo` int(255) NOT NULL default \'0\',
     `nrofcrime` int(10) NOT NULL default \'0\',
     `nrofcar` int(10) NOT NULL default \'0\',
     `nrofroute` int(10) NOT NULL default \'0\',
     `nrofoc` int(10) NOT NULL default \'0\',
     `nrofrace` int(10) NOT NULL default \'0\',
     `nrofkill` int(10) NOT NULL default \'0\',
     `testament` varchar(16) NOT NULL default \'\',
     `huwelijk` varchar(16) NOT NULL default \'\',
     `bf` int(10) NOT NULL default \'0\',
     `lang` char(2) NOT NULL default \'nl\',
     `paid` int(1) NOT NULL default \'0\',
     `paidtime1` int(20) NOT NULL default \'0\',
     `paidtime2` int(20) NOT NULL default \'0\',
     `paidtime3` int(20) NOT NULL default \'0\',
     `ah` int(1) NOT NULL default \'0\',
     `dh` int(1) NOT NULL default \'0\',
     `gstart` int(2) NOT NULL default \'0\',
     PRIMARY KEY  (`id`),
     UNIQUE KEY `login` (`login`),
     KEY `id` (`id`)
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `users`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `vermoord`
    #
     
    CREATE TABLE `vermoord` (
     `login` varchar(16) NOT NULL default \'\',
     `dader` varchar(16) NOT NULL default \'\',
     `date` datetime NOT NULL default \'0000-00-00 00:00:00\',
     `msg` text NOT NULL
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `vermoord`
    #
     
    # --------------------------------------------------------
     
    #
    # Tabel structuur voor tabel `ws`
    #
     
    CREATE TABLE `ws` (
     `id` varchar(20) NOT NULL default \'\',
     `login` varchar(16) NOT NULL default \'\',
     `victim` varchar(16) NOT NULL default \'\',
     `suspect` varchar(16) NOT NULL default \'\',
     `prijs` varchar(255) NOT NULL default \'\',
     `status` int(1) NOT NULL default \'0\',
     `time` datetime NOT NULL default \'0000-00-00 00:00:00\'
    ) type=MyISAM;
     
    #
    # Gegevens worden uitgevoerd voor tabel `ws`
    #
     
     

create a new version of this paste RAW Paste Data
SQL: # # Tabel structuur voor tabel `autorace` # CREATE TABLE `autorace` ( `login` varchar(16) NOT NULL default \'\', `enemy` varchar(16) NOT NULL default \'\', `id1` varchar(5) NOT NULL default \'\', `id2` varchar(5) NOT NULL default \'\', `ready1` char(1) NOT NULL default \'\', `ready2` char(1) NOT NULL default \'\', `stad` varchar(25) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `autorace` # # -------------------------------------------------------- # # Tabel structuur voor tabel `backup-users` # CREATE TABLE `backup-users` ( `id` int(4) NOT NULL auto_increment, `login` varchar(16) NOT NULL default \'\', `start` datetime NOT NULL default \'0000-00-00 00:00:00\', `online` datetime NOT NULL default \'0000-00-00 00:00:00\', `pass` varchar(50) NOT NULL default \'\', `email` varchar(255) NOT NULL default \'\', `ip` varchar(255) NOT NULL default \'\', `stad` varchar(100) NOT NULL default \'Brussel\', `geslacht` varchar(20) NOT NULL default \'\', `activated` char(1) NOT NULL default \'0\', `safe` datetime NOT NULL default \'0000-00-00 00:00:00\', `health` int(3) NOT NULL default \'100\', `xp` int(6) NOT NULL default \'0\', `ac` datetime NOT NULL default \'0000-00-00 00:00:00\', `crime` datetime NOT NULL default \'0000-00-00 00:00:00\', `bc` datetime NOT NULL default \'0000-00-00 00:00:00\', `pc` datetime NOT NULL default \'0000-00-00 00:00:00\', `play` int(255) NOT NULL default \'0\', `zak` int(10) NOT NULL default \'1000\', `bank` int(10) NOT NULL default \'0\', `banktime` int(15) NOT NULL default \'0\', `respect` int(10) NOT NULL default \'0\', `se` decimal(4,1) NOT NULL default \'0.0\', `famillie` varchar(20) NOT NULL default \'\', `famrang` char(1) NOT NULL default \'0\', `famcapo` varchar(25) NOT NULL default \'\', `trans` char(2) NOT NULL default \'0\', `kogels` int(10) NOT NULL default \'0\', `wapon` char(2) NOT NULL default \'0\', `guard` int(2) NOT NULL default \'0\', `defence` int(2) NOT NULL default \'0\', `level` varchar(4) NOT NULL default \'1\', `rp` int(3) NOT NULL default \'0\', `kc` datetime NOT NULL default \'0000-00-00 00:00:00\', `status` varchar(10) NOT NULL default \'levend\', `drugs` char(2) NOT NULL default \'0\', `drank` char(2) NOT NULL default \'0\', `drugst` datetime NOT NULL default \'0000-00-00 00:00:00\', `drankt` datetime NOT NULL default \'0000-00-00 00:00:00\', `energie` decimal(3,1) NOT NULL default \'99.9\', `slaap` datetime NOT NULL default \'0000-00-00 00:00:00\', `sl` char(1) NOT NULL default \'0\', `vet` char(3) NOT NULL default \'0\', `Brussel` char(1) NOT NULL default \'0\', `transport` datetime NOT NULL default \'0000-00-00 00:00:00\', `Leuven` char(1) NOT NULL default \'0\', `Gent` char(1) NOT NULL default \'0\', `Brugge` char(1) NOT NULL default \'0\', `Hasselt` char(1) NOT NULL default \'0\', `Antwerpen` char(1) NOT NULL default \'0\', `Amsterdam` char(1) NOT NULL default \'0\', `Enschede` char(1) NOT NULL default \'\', `pic` varchar(255) NOT NULL default \'\', `info` text NOT NULL, `bo` int(255) NOT NULL default \'0\', `nrofcrime` int(10) NOT NULL default \'0\', `nrofcar` int(10) NOT NULL default \'0\', `nrofroute` int(10) NOT NULL default \'0\', `nrofoc` int(10) NOT NULL default \'0\', `nrofrace` int(10) NOT NULL default \'0\', `nrofkill` int(10) NOT NULL default \'0\', `testament` varchar(16) NOT NULL default \'\', `huwelijk` varchar(16) NOT NULL default \'\', `bf` int(10) NOT NULL default \'0\', `lang` char(2) NOT NULL default \'nl\', `paid` char(1) NOT NULL default \'0\', `ah` int(1) NOT NULL default \'0\', `dh` int(1) NOT NULL default \'0\', `gstart` int(2) NOT NULL default \'0\', PRIMARY KEY (`id`), UNIQUE KEY `login` (`login`), KEY `id` (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `backup-users` # # -------------------------------------------------------- # # Tabel structuur voor tabel `bans` # CREATE TABLE `bans` ( `ip` varchar(255) NOT NULL default \'\', `reden` varchar(255) NOT NULL default \'\', `door` varchar(16) NOT NULL default \'\', `login` varchar(16) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `bans` # # -------------------------------------------------------- # # Tabel structuur voor tabel `blackjack` # CREATE TABLE `blackjack` ( `id` int(4) NOT NULL auto_increment, `login` varchar(16) NOT NULL default \'\', `inzet` int(14) NOT NULL default \'0\', `kaart` int(2) NOT NULL default \'0\', `kaartpic` longtext NOT NULL, `aas` int(2) NOT NULL default \'0\', `dealer` int(2) NOT NULL default \'0\', `dealerpic` varchar(255) NOT NULL default \'\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `blackjack` # # -------------------------------------------------------- # # Tabel structuur voor tabel `cars` # CREATE TABLE `cars` ( `naam` varchar(16) NOT NULL default \'\', `url` varchar(255) NOT NULL default \'\', `waarde` varchar(6) NOT NULL default \'\', `auto` varchar(255) NOT NULL default \'\', FULLTEXT KEY `naam` (`naam`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `cars` # INSERT INTO `cars` VALUES (\'45000\', \'images/autos/buick_cabrio.jpg\', \'45000\', \'Buick Cabrio\'); INSERT INTO `cars` VALUES (\'40000\', \'images/autos/aston_martin_db3s.jpg\', \'40000\', \'Aston Martin DB3S\'); INSERT INTO `cars` VALUES (\'30000\', \'images/autos/ford_fairlane.jpg\', \'30000\', \'Ford Fairlane\'); INSERT INTO `cars` VALUES (\'10000\', \'images/autos/pontiac.jpg\', \'10000\', \'Pontiac\'); INSERT INTO `cars` VALUES (\'25000\', \'images/autos/dodge_kingsway.jpg\', \'25000\', \'Dodge Kingsway\'); INSERT INTO `cars` VALUES (\'60000\', \'images/autos/chrysler_converible.jpg\', \'60000\', \'Chrysler Converible\'); INSERT INTO `cars` VALUES (\'65000\', \'images/autos/pontiac_bonneville.jpg\', \'65000\', \'Pontiac Bonneville\'); INSERT INTO `cars` VALUES (\'80000\', \'images/autos/dodge_dart.jpg\', \'80000\', \'Dodge Dart\'); INSERT INTO `cars` VALUES (\'95000\', \'images/autos/mercury_cougar.jpg\', \'95000\', \'Mercury Cougar\'); INSERT INTO `cars` VALUES (\'125000\', \'images/autos/chevy_camaro_ss.jpg\', \'125000\', \'Chevy Camaro SS\'); INSERT INTO `cars` VALUES (\'150000\', \'images/autos/ford_mustang.jpg\', \'150000\', \'Ford Mustang\'); INSERT INTO `cars` VALUES (\'15000\', \'images/autos/caddillac_sedan.jpg\', \'15000\', \'Caddillac Sedan\'); INSERT INTO `cars` VALUES (\'175000\', \'images/autos/alfa_romeo _fnm_2000_jk.jpg\', \'175000\', \'Alfa Romeo FNM 2000 JK\'); INSERT INTO `cars` VALUES (\'200000\', \'images/autos/porsche_356b_cabriolet.jpg\', \'200000\', \'Porsche 356B Cabriolet\'); INSERT INTO `cars` VALUES (\'400000\', \'images/autos/streamliner.jpg\', \'400000\', \'Mercedes W124 Avus Streamling\'); # -------------------------------------------------------- # # Tabel structuur voor tabel `casino` # CREATE TABLE `casino` ( `id` int(4) NOT NULL auto_increment, `spel` varchar(255) NOT NULL default \'\', `owner` varchar(16) NOT NULL default \'\', `stad` varchar(20) NOT NULL default \'\', `winst` varchar(7) NOT NULL default \'0\', `inzet` varchar(7) NOT NULL default \'1000\', `status` int(1) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `casino` # # -------------------------------------------------------- # # Tabel structuur voor tabel `cron` # CREATE TABLE `cron` ( `name` varchar(16) NOT NULL default \'\', `time` datetime NOT NULL default \'0000-00-00 00:00:00\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `cron` # INSERT INTO `cron` VALUES (\'kogels\', \'0000-00-00 00:00:00\'); INSERT INTO `cron` VALUES (\'week\', \'0000-00-00 00:00:00\'); INSERT INTO `cron` VALUES (\'day\', \'0000-00-00 00:00:00\'); INSERT INTO `cron` VALUES (\'loterij\', \'0000-00-00 00:00:00\'); INSERT INTO `cron` VALUES (\'uur\', \'0000-00-00 00:00:00\'); # -------------------------------------------------------- # # Tabel structuur voor tabel `detectives` # CREATE TABLE `detectives` ( `van` varchar(16) NOT NULL default \'\', `naar` varchar(16) NOT NULL default \'\', `stad` varchar(30) NOT NULL default \'\', `time` datetime NOT NULL default \'0000-00-00 00:00:00\', `id` int(4) NOT NULL auto_increment, PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `detectives` # # -------------------------------------------------------- # # Tabel structuur voor tabel `donate` # CREATE TABLE `donate` ( `id` int(10) NOT NULL auto_increment, `door` varchar(25) NOT NULL default \'\', `code` varchar(255) NOT NULL default \'\', `status` int(1) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `donate` # # -------------------------------------------------------- # # Tabel structuur voor tabel `famillie` # CREATE TABLE `famillie` ( `name` varchar(16) NOT NULL default \'\', `info` text NOT NULL, `crusher` int(15) NOT NULL default \'0\', `aantal` int(4) NOT NULL default \'0\', `stad` varchar(20) NOT NULL default \'\', `pic` varchar(255) NOT NULL default \'\', `bank` int(7) NOT NULL default \'0\', `grond` int(5) NOT NULL default \'50\', `rang2` int(20) NOT NULL default \'0\', `rang3` int(20) NOT NULL default \'0\', `rang4` int(20) NOT NULL default \'0\', `rang5` int(20) NOT NULL default \'0\', `rang6` int(20) NOT NULL default \'0\', `rang7` int(20) NOT NULL default \'0\', `rang8` int(20) NOT NULL default \'0\', `rang9` int(20) NOT NULL default \'0\', `rang10` int(20) NOT NULL default \'0\', `rang11` int(20) NOT NULL default \'0\', `rang12` int(20) NOT NULL default \'0\', `rang13` int(20) NOT NULL default \'0\', `rang14` int(20) NOT NULL default \'0\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `famillie` # # -------------------------------------------------------- # # Tabel structuur voor tabel `forum_reacties` # CREATE TABLE `forum_reacties` ( `id` int(10) unsigned NOT NULL auto_increment, `topic_id` int(10) NOT NULL default \'0\', `user` varchar(30) NOT NULL default \'\', `subject` varchar(50) NOT NULL default \'\', `message` text NOT NULL, `date` datetime NOT NULL default \'0000-00-00 00:00:00\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `forum_reacties` # # -------------------------------------------------------- # # Tabel structuur voor tabel `forum_topics` # CREATE TABLE `forum_topics` ( `id` int(10) unsigned NOT NULL auto_increment, `type` varchar(255) NOT NULL default \'\', `user` varchar(30) NOT NULL default \'\', `subject` varchar(50) NOT NULL default \'\', `message` text NOT NULL, `date` datetime NOT NULL default \'0000-00-00 00:00:00\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `forum_topics` # # -------------------------------------------------------- # # Tabel structuur voor tabel `friends` # CREATE TABLE `friends` ( `login` varchar(16) NOT NULL default \'\', `friend` varchar(16) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `friends` # # -------------------------------------------------------- # # Tabel structuur voor tabel `garage` # CREATE TABLE `garage` ( `id` int(4) NOT NULL auto_increment, `login` varchar(16) NOT NULL default \'\', `naam` varchar(16) NOT NULL default \'\', `waarde` int(6) NOT NULL default \'0\', `damage` char(3) NOT NULL default \'\', `stad` varchar(16) NOT NULL default \'\', `safe` char(1) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `garage` # # -------------------------------------------------------- # # Tabel structuur voor tabel `hitlist` # CREATE TABLE `hitlist` ( `login` varchar(16) NOT NULL default \'\', `suspect` varchar(16) NOT NULL default \'\', `prijs` int(9) NOT NULL default \'0\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `hitlist` # # -------------------------------------------------------- # # Tabel structuur voor tabel `invite` # CREATE TABLE `invite` ( `login` varchar(16) NOT NULL default \'\', `famillie` varchar(16) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `invite` # # -------------------------------------------------------- # # Tabel structuur voor tabel `iplog` # CREATE TABLE `iplog` ( `login` varchar(16) NOT NULL default \'\', `ip` varchar(255) NOT NULL default \'\', `time` datetime NOT NULL default \'0000-00-00 00:00:00\', `allo` char(1) NOT NULL default \'0\', `status` varchar(255) NOT NULL default \'levend\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `iplog` # # -------------------------------------------------------- # # Tabel structuur voor tabel `items` # CREATE TABLE `items` ( `id` int(4) NOT NULL auto_increment, `nr` char(1) NOT NULL default \'\', `type` varchar(5) NOT NULL default \'\', `naam` varchar(16) NOT NULL default \'\', `aprijs` int(10) NOT NULL default \'0\', `vprijs` int(10) NOT NULL default \'0\', `effect` decimal(10,2) NOT NULL default \'0.00\', PRIMARY KEY (`id`) ) type=MyISAM AUTO_INCREMENT=18 ; # # Gegevens worden uitgevoerd voor tabel `items` # INSERT INTO `items` VALUES (1, \'1\', \'att\', \'Uzi\', 25000, 20000, \'3.00\'); INSERT INTO `items` VALUES (2, \'2\', \'att\', \'M16\', 50000, 40000, \'2.00\'); INSERT INTO `items` VALUES (3, \'1\', \'trans\', \'Treinabbonnement\', 250000, 200000, \'3600.00\'); INSERT INTO `items` VALUES (4, \'3\', \'trans\', \'Limousine\', 1000000, 750000, \'1800.00\'); INSERT INTO `items` VALUES (7, \'4\', \'att\', \'Sniper Rifle\', 100000, 80000, \'1.50\'); INSERT INTO `items` VALUES (8, \'2\', \'trans\', \'Taxi\', 750000, 600000, \'2400.00\'); INSERT INTO `items` VALUES (9, \'3\', \'att\', \'9mm\', 10000, 8000, \'4.00\'); INSERT INTO `items` VALUES (10, \'4\', \'trans\', \'Privé-Jet\', 1500000, 1200000, \'900.00\'); INSERT INTO `items` VALUES (13, \'6\', \'att\', \'Tommy Gun\', 140000, 120000, \'2.00\'); INSERT INTO `items` VALUES (14, \'5\', \'att\', \'Magnum Semi Auto\', 75000, 60000, \'1.99\'); INSERT INTO `items` VALUES (15, \'0\', \'def\', \'Geen bescherming\', 0, 0, \'1.00\'); INSERT INTO `items` VALUES (16, \'2\', \'def\', \'Kogelvrije vest\', 100000, 75000, \'4.00\'); INSERT INTO `items` VALUES (17, \'1\', \'def\', \'Kogelvrij schild\', 30000, 20000, \'2.00\'); # -------------------------------------------------------- # # Tabel structuur voor tabel `jail` # CREATE TABLE `jail` ( `login` varchar(16) NOT NULL default \'\', `boete` varchar(6) NOT NULL default \'\', `time` datetime NOT NULL default \'0000-00-00 00:00:00\', `stad` varchar(255) NOT NULL default \'\', `famillie` varchar(255) NOT NULL default \'\', `bo` int(1) NOT NULL default \'0\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `jail` # # -------------------------------------------------------- # # Tabel structuur voor tabel `kras` # CREATE TABLE `kras` ( `login` varchar(16) NOT NULL default \'\', `aantal` int(1) NOT NULL default \'0\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `kras` # # -------------------------------------------------------- # # Tabel structuur voor tabel `log` # CREATE TABLE `log` ( `code` varchar(255) NOT NULL default \'\', `wat` varchar(255) NOT NULL default \'\', `aantal` int(255) NOT NULL default \'0\', `van` varchar(16) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `log` # # -------------------------------------------------------- # # Tabel structuur voor tabel `logs` # CREATE TABLE `logs` ( `time` datetime default NULL, `login` varchar(16) default NULL, `person` varchar(16) default NULL, `code` int(10) default NULL, `area` varchar(32) default NULL, `com` varchar(255) NOT NULL default \'Geen\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `logs` # # -------------------------------------------------------- # # Tabel structuur voor tabel `loterij` # CREATE TABLE `loterij` ( `id` int(100) NOT NULL auto_increment, `login` varchar(255) NOT NULL, PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `loterij` # # -------------------------------------------------------- # # Tabel structuur voor tabel `messages` # CREATE TABLE `messages` ( `id` int(4) NOT NULL auto_increment, `from` varchar(16) NOT NULL default \'\', `to` varchar(16) NOT NULL default \'\', `subject` varchar(50) NOT NULL default \'\', `message` text NOT NULL, `time` datetime NOT NULL default \'0000-00-00 00:00:00\', `read` int(1) NOT NULL default \'0\', `inbox` int(1) NOT NULL default \'0\', `save` int(1) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `messages` # # -------------------------------------------------------- # # Tabel structuur voor tabel `mshop` # CREATE TABLE `mshop` ( `id` int(10) NOT NULL auto_increment, `door` varchar(25) NOT NULL default \'\', `type` varchar(10) NOT NULL default \'\', `specs` varchar(255) NOT NULL default \'\', `bieder` varchar(25) NOT NULL default \'\', `aflooptijd` int(15) NOT NULL default \'0\', `specs2` varchar(255) NOT NULL default \'\', `specs3` varchar(255) NOT NULL default \'\', `bod` int(15) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `mshop` # # -------------------------------------------------------- # # Tabel structuur voor tabel `multiple` # CREATE TABLE `multiple` ( `ip` varchar(255) NOT NULL default \'1\', `allo` char(1) NOT NULL default \'1\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `multiple` # # -------------------------------------------------------- # # Tabel structuur voor tabel `news` # CREATE TABLE `news` ( `id` int(4) NOT NULL auto_increment, `title` varchar(100) NOT NULL default \'\', `text` text NOT NULL, `time` int(15) NOT NULL default \'0\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `news` # # -------------------------------------------------------- # # Tabel structuur voor tabel `oc` # CREATE TABLE `oc` ( `id` int(100) NOT NULL auto_increment, `login` varchar(100) default NULL, `we` varchar(100) default NULL, `be` varchar(100) default NULL, `dr` varchar(100) default NULL, `ready1` int(1) NOT NULL default \'0\', `ready2` int(1) NOT NULL default \'0\', `ready3` int(1) NOT NULL default \'0\', `wapens` varchar(100) NOT NULL default \'0\', `kogels` int(100) NOT NULL default \'0\', `bommen` varchar(100) NOT NULL default \'0\', `aantal` int(100) NOT NULL default \'0\', `auto` varchar(100) NOT NULL default \'\', `klaar` int(1) NOT NULL default \'0\', `autoid` int(255) NOT NULL default \'0\', `damage` char(3) NOT NULL default \'\', `stad` varchar(255) NOT NULL default \'\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `oc` # # -------------------------------------------------------- # # Tabel structuur voor tabel `poll` # CREATE TABLE `poll` ( `id` int(11) NOT NULL auto_increment, `vraag` varchar(200) NOT NULL default \'\', `actief` int(1) NOT NULL default \'0\', `datum` int(20) NOT NULL default \'0\', `keuze1` varchar(50) NOT NULL default \'\', `keuze2` varchar(50) NOT NULL default \'\', `keuze3` varchar(50) NOT NULL default \'\', `keuze4` varchar(50) NOT NULL default \'\', `keuze5` varchar(50) NOT NULL default \'\', `keuze6` varchar(50) NOT NULL default \'\', `keuze7` varchar(50) NOT NULL default \'\', `keuze8` varchar(50) NOT NULL default \'\', `keuze9` varchar(50) NOT NULL default \'\', `keuze10` varchar(50) NOT NULL default \'\', `antwoord1` int(11) NOT NULL default \'0\', `antwoord2` int(11) NOT NULL default \'0\', `antwoord3` int(11) NOT NULL default \'0\', `antwoord4` int(11) NOT NULL default \'0\', `antwoord5` int(11) NOT NULL default \'0\', `antwoord6` int(11) NOT NULL default \'0\', `antwoord7` int(11) NOT NULL default \'0\', `antwoord8` int(11) NOT NULL default \'0\', `antwoord9` int(11) NOT NULL default \'0\', `antwoord10` int(11) NOT NULL default \'0\', `gestemd` text NOT NULL, PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `poll` # # -------------------------------------------------------- # # Tabel structuur voor tabel `route66` # CREATE TABLE `route66` ( `login` varchar(16) NOT NULL default \'\', `driver` varchar(16) NOT NULL default \'\', `stad` varchar(255) NOT NULL default \'\', `ready1` char(1) NOT NULL default \'\', `ready2` char(1) NOT NULL default \'\', `car` int(4) NOT NULL default \'0\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `route66` # # -------------------------------------------------------- # # Tabel structuur voor tabel `shame` # CREATE TABLE `shame` ( `id` int(4) NOT NULL auto_increment, `time` datetime NOT NULL default \'0000-00-00 00:00:00\', `cheater` varchar(16) NOT NULL default \'\', `person` varchar(16) NOT NULL default \'\', `com` varchar(255) NOT NULL default \'\', PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `shame` # # -------------------------------------------------------- # # Tabel structuur voor tabel `stad` # CREATE TABLE `stad` ( `stad` varchar(255) NOT NULL default \'\', `kogels` int(4) NOT NULL default \'500\', `prijs` int(5) NOT NULL default \'1000\', `drugs` char(3) NOT NULL default \'50\', `drank` char(3) NOT NULL default \'50\', `drugsp` varchar(5) NOT NULL default \'2500\', `drankp` varchar(4) NOT NULL default \'2500\', `transp` varchar(5) NOT NULL default \'2000\', `grond` int(5) NOT NULL default \'1000\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `stad` # INSERT INTO `stad` VALUES (\'Brussel\', 100, 1273, \'32\', \'464\', \'14903\', \'3402\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Leuven\', 100, 1273, \'194\', \'196\', \'13734\', \'1093\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Gent\', 100, 1273, \'104\', \'17\', \'7049\', \'5630\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Hasselt\', 100, 1273, \'381\', \'132\', \'12659\', \'1312\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Antwerpen\', 100, 1273, \'33\', \'28\', \'6172\', \'4550\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Brugge\', 100, 1273, \'331\', \'6\', \'7190\', \'3668\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Amsterdam\', 100, 1273, \'194\', \'4\', \'12955\', \'3408\', \'2000\', 1000); INSERT INTO `stad` VALUES (\'Enschede\', 100, 1273, \'544\', \'0\', \'10080\', \'2803\', \'2000\', 1000); # -------------------------------------------------------- # # Tabel structuur voor tabel `temp` # CREATE TABLE `temp` ( `id` int(16) NOT NULL auto_increment, `time` datetime default NULL, `login` varchar(16) default NULL, `IP` varchar(32) default NULL, `forwardedFor` varchar(32) NOT NULL default \'\', `code` int(10) unsigned NOT NULL default \'0\', `area` varchar(32) default NULL, PRIMARY KEY (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `temp` # # -------------------------------------------------------- # # Tabel structuur voor tabel `trouwen` # CREATE TABLE `trouwen` ( `login` varchar(16) NOT NULL default \'\', `partner` varchar(16) NOT NULL default \'\', `stad` varchar(255) NOT NULL default \'\', `ready1` char(1) NOT NULL default \'\', `ready2` char(1) NOT NULL default \'\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `trouwen` # # -------------------------------------------------------- # # Tabel structuur voor tabel `users` # CREATE TABLE `users` ( `id` int(4) NOT NULL auto_increment, `login` varchar(16) NOT NULL default \'\', `start` datetime NOT NULL default \'0000-00-00 00:00:00\', `online` datetime NOT NULL default \'0000-00-00 00:00:00\', `pass` varchar(50) NOT NULL default \'\', `email` varchar(255) NOT NULL default \'\', `ip` varchar(255) NOT NULL default \'\', `stad` varchar(100) NOT NULL default \'Brussel\', `geslacht` varchar(20) NOT NULL default \'\', `activated` char(1) NOT NULL default \'0\', `safe` datetime NOT NULL default \'0000-00-00 00:00:00\', `health` int(3) NOT NULL default \'100\', `xp` int(6) NOT NULL default \'0\', `ac` datetime NOT NULL default \'0000-00-00 00:00:00\', `crime` datetime NOT NULL default \'0000-00-00 00:00:00\', `bc` datetime NOT NULL default \'0000-00-00 00:00:00\', `pc` datetime NOT NULL default \'0000-00-00 00:00:00\', `play` int(255) NOT NULL default \'0\', `zak` int(10) NOT NULL default \'1000\', `bank` int(10) NOT NULL default \'0\', `banktime` int(15) NOT NULL default \'0\', `respect` int(10) NOT NULL default \'0\', `se` decimal(4,1) NOT NULL default \'0.0\', `famillie` varchar(20) NOT NULL default \'\', `famrang` char(1) NOT NULL default \'0\', `famcapo` varchar(25) NOT NULL default \'\', `trans` char(2) NOT NULL default \'0\', `kogels` int(10) NOT NULL default \'0\', `wapon` char(2) NOT NULL default \'0\', `guard` int(2) NOT NULL default \'0\', `defence` int(2) NOT NULL default \'0\', `level` varchar(4) NOT NULL default \'1\', `rp` int(3) NOT NULL default \'0\', `kc` datetime NOT NULL default \'0000-00-00 00:00:00\', `status` varchar(10) NOT NULL default \'levend\', `drugs` char(2) NOT NULL default \'0\', `drank` char(2) NOT NULL default \'0\', `drugst` datetime NOT NULL default \'0000-00-00 00:00:00\', `drankt` datetime NOT NULL default \'0000-00-00 00:00:00\', `energie` decimal(3,1) NOT NULL default \'99.9\', `slaap` datetime NOT NULL default \'0000-00-00 00:00:00\', `sl` char(1) NOT NULL default \'0\', `vet` char(3) NOT NULL default \'0\', `Brussel` char(1) NOT NULL default \'0\', `transport` datetime NOT NULL default \'0000-00-00 00:00:00\', `Leuven` char(1) NOT NULL default \'0\', `Gent` char(1) NOT NULL default \'0\', `Brugge` char(1) NOT NULL default \'0\', `Hasselt` char(1) NOT NULL default \'0\', `Antwerpen` char(1) NOT NULL default \'0\', `Amsterdam` char(1) NOT NULL default \'0\', `Enschede` char(1) NOT NULL default \'\', `pic` varchar(255) NOT NULL default \'\', `info` text NOT NULL, `bo` int(255) NOT NULL default \'0\', `nrofcrime` int(10) NOT NULL default \'0\', `nrofcar` int(10) NOT NULL default \'0\', `nrofroute` int(10) NOT NULL default \'0\', `nrofoc` int(10) NOT NULL default \'0\', `nrofrace` int(10) NOT NULL default \'0\', `nrofkill` int(10) NOT NULL default \'0\', `testament` varchar(16) NOT NULL default \'\', `huwelijk` varchar(16) NOT NULL default \'\', `bf` int(10) NOT NULL default \'0\', `lang` char(2) NOT NULL default \'nl\', `paid` int(1) NOT NULL default \'0\', `paidtime1` int(20) NOT NULL default \'0\', `paidtime2` int(20) NOT NULL default \'0\', `paidtime3` int(20) NOT NULL default \'0\', `ah` int(1) NOT NULL default \'0\', `dh` int(1) NOT NULL default \'0\', `gstart` int(2) NOT NULL default \'0\', PRIMARY KEY (`id`), UNIQUE KEY `login` (`login`), KEY `id` (`id`) ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `users` # # -------------------------------------------------------- # # Tabel structuur voor tabel `vermoord` # CREATE TABLE `vermoord` ( `login` varchar(16) NOT NULL default \'\', `dader` varchar(16) NOT NULL default \'\', `date` datetime NOT NULL default \'0000-00-00 00:00:00\', `msg` text NOT NULL ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `vermoord` # # -------------------------------------------------------- # # Tabel structuur voor tabel `ws` # CREATE TABLE `ws` ( `id` varchar(20) NOT NULL default \'\', `login` varchar(16) NOT NULL default \'\', `victim` varchar(16) NOT NULL default \'\', `suspect` varchar(16) NOT NULL default \'\', `prijs` varchar(255) NOT NULL default \'\', `status` int(1) NOT NULL default \'0\', `time` datetime NOT NULL default \'0000-00-00 00:00:00\' ) type=MyISAM; # # Gegevens worden uitgevoerd voor tabel `ws` #
Pastebin.com Tools & Applications
iPhone/iPad Windows Firefox Chrome WebOS Android Mac Opera Click.to UNIX WinPhone
create new paste  |  api  |  trends  |  users  |  faq  |  tools  |  domains center  |  privacy  |  contact  |  stats  |  go pro
Follow us: pastebin on facebook  |  pastebin on twitter  |  pastebin in the news
Some friends: hostshut  |  fileshut  |  hostlogr  |  w3patrol
Pastebin v3.1 rendered in: 0.036 seconds
New Proxy Site 