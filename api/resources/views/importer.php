<?php

// OFF SWITCH
print "Turned off\n"; return;

require_once "db/dbw.php";
$filename = 'upload/mhimport.csv';
if (!file_exists($filename)) {
    print "File $filename doesn't exist...\n";
    return;
}
$file = fopen($filename, "r");
$num_rows = 0;

// TRUNCATE ALL TABLES
$truncate_tables = array(
    'mice',
    'cheeses',
    'locations',
    'mice_cheeses',
    'mice_locations',
);

foreach ($truncate_tables as $table) {
    $result = $db->prepare("TRUNCATE $table");
    $result->execute();
}

// Iceberg stages
static $iceberg_mice_stages = array(
    'GENERAL DRHELLER'          => array('GENERALS'),
    'LADY COLDSNAP'             => array('GENERALS'),
    'LORD SPLODINGTON'          => array('GENERALS'),
    'PRINCESS FIST'             => array('GENERALS'),
    'LIVING SALT'               => array('0-300FT', '301-600FT', '601-1600FT', '1601-1800FT', '1801-2000FT'),
    'POLAR BEAR'                => array('0-300FT', '301-600FT'),
    'SNOW SLINGER'              => array('0-300FT', '301-600FT', '601-1600FT'),
    'CHIPPER'                   => array('0-300FT', '601-1600FT', '1601-1800FT'),
    'ICEBREAKER'                => array('0-300FT', '601-1600FT'),
    'INCOMPETENT ICE CLIMBER'   => array('0-300FT'),
    'SNOW SOLDIER'              => array('0-300FT'),
    'ICEBLOCK'                  => array('301-600FT', '601-1600FT', '1601-1800FT'),
    'MAMMOTH'                   => array('301-600FT'),
    'SNOW BOWLER'               => array('301-600FT', '601-1600FT', '1601-1800FT'),
    'YETI'                      => array('301-600FT'),
    'HEAVY BLASTER'             => array('601-1600FT'),
    'SABOTEUR'                  => array('601-1600FT'),
    'STICKYBOMBER'              => array('601-1600FT'),
    'WOLFSKIE'                  => array('601-1600FT', '1601-1800FT'),
    'ICEBLADE'                  => array('1601-1800FT'),
    'SNOWBLIND'                 => array('1601-1800FT'),
    'WATER WIELDER'             => array('1601-1800FT'),
    'FROSTLANCE GUARD'          => array('1801-2000FT'),
    'FROSTWING COMMANDER'       => array('1801-2000FT'),
    'ICEWING'                   => array('1801-2000FT'),
    'DEEP'                      => array('2000FT'),
);

// Balack's Cove stages
static $balacks_cove_mice_stages = array(
    'BALACK THE BANISHED'   => array('LOW TIDE', 'MEDIUM TIDE'),
    'BRIMSTONE'             => array('LOW TIDE', 'MEDIUM TIDE'),
    'DAVY JONES'            => array('LOW TIDE', 'MEDIUM TIDE'),
    'DERR LICH'             => array('LOW TIDE', 'MEDIUM TIDE'),
    'ELUB LICH'             => array('LOW TIDE', 'MEDIUM TIDE'),
    'ENSLAVED SPIRIT'       => array('LOW TIDE', 'MEDIUM TIDE'),
    'NERG LICH'             => array('LOW TIDE', 'MEDIUM TIDE'),
    'RIPTIDE'               => array('MEDIUM TIDE', 'HIGH TIDE'),
    'TIDAL FISHER'          => array('LOW TIDE'),
    'TWISTED FIEND'         => array('LOW TIDE', 'MEDIUM TIDE'),
);

// Gnawnian Express stages
static $gnawnian_express_mice_stages = array(
    'ANGRY TRAIN STAFF'         => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'BARTENDER'                 => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'FARRIER'                   => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'MYSTERIOUS TRAVELLER'      => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'PARLOUR PLAYER'            => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'PASSENGER'                 => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'PHOTOGRAPHER'              => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'STOWAWAY'                  => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'STUFFY BANKER'             => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'TONIC SALESMAN'            => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'TRAIN CONDUCTOR'           => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'TRAVELLING BARBER'         => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'UPPER CLASS LADY'          => array('STATION', '1ST PHASE', '2ND PHASE', '3RD PHASE'),
    'CRATE CAMO'                => array('1ST PHASE'),
    'CUTE CRATE CARRIER'        => array('1ST PHASE'),
    'SUPPLY HOARDER'            => array('1ST PHASE'),
    'WAREHOUSE MANAGER'         => array('1ST PHASE'),
    'AUTOMORAT'                 => array('2ND PHASE'),
    'CANNONBALL'                => array('2ND PHASE'),
    'DANGEROUS DUO'             => array('2ND PHASE'),
    'HOOKSHOT'                  => array('2ND PHASE'),
    'MOUSE WITH NO NAME'        => array('2ND PHASE'),
    'SHARPSHOOTER'              => array('2ND PHASE'),
    'STEEL HORSE RIDER'         => array('2ND PHASE'),
    'STOUTGEAR'                 => array('2ND PHASE'),
    'BLACK POWDER THIEF'        => array('3RD PHASE'),
    'COAL SHOVELLER'            => array('3RD PHASE'),
    'FUEL'                      => array('3RD PHASE'),
    'MAGMATIC CRYSTAL THIEF'    => array('3RD PHASE'),
    'MAGMATIC GOLEM'            => array('3RD PHASE'),
    'TRAIN ENGINEER'            => array('3RD PHASE'),
);

// Sunken stages
static $sunken_city_mice_stages = array(
    'CITY NOBLE'            => array('DOCKED'),
    'CITY WORKER'           => array('DOCKED'),
    'CLUMSY CARRIER'        => array('DOCKED'),
    'ELITE GUARDIAN'        => array('DOCKED'),
    'ENGINSEER'             => array('DOCKED'),
    'HYDROLOGIST'           => array('DOCKED'),
    'OXYGEN BARON'          => array('DOCKED'),
    'SUNKEN CITIZEN'        => array('DOCKED'),
    'BARNACLE BEAUTICIAN'   => array('0-2KM', '2-10KM'),
    'BOTTOM FEEDER'         => array('0-2KM'),
    'CRABOLIA'              => array('0-2KM', '2-10KM'),
    'DEEP SEA DIVER'        => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'DERANGED DECKHAND'     => array('10-15KM', '15-25KM', '25KM+'),
    'DREAD PIRATE MOUSERT'  => array('2-10KM'),
    'PIRATE ANCHOR'         => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'SUNKEN BANSHEE'        => array('10-15KM', '15-25KM', '25KM+'),
    'SWASHBLADE'            => array('10-15KM', '15-25KM', '25KM+'),
    'CORAL'                 => array('0-2KM', '2-10KM'),
    'CORAL CUDDLER'         => array('0-2KM'),
    'CORAL DRAGON'          => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'CORAL GARDENER'        => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'CORAL GUARD'           => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'CORAL HARVESTER'       => array('2-10KM'),
    'CORAL QUEEN'           => array('10-15KM', '15-25KM', '25KM+'),
    'SEADRAGON'             => array('0-2KM', '2-10KM'),
    'TURRET GUARD'          => array('10-15KM', '15-25KM', '25KM+'),
    'ANGELFISH'             => array('10-15KM', '15-25KM', '25KM+'),
    'BETTA'                 => array('10-15KM', '15-25KM', '25KM+'),
    'CLOWNFISH'             => array('0-2KM'),
    'CUTTLE'                => array('0-2KM', '2-10KM'),
    'EEL'                   => array('10-15KM', '15-25KM', '25KM+'),
    'JELLYFISH'             => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'KOIMAID'               => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'MANATEE'               => array('0-2KM', '2-10KM'),
    'MLOUNDER FLOUNDER'     => array('0-2KM'),
    'PUFFER'                => array('0-2KM', '2-10KM'),
    'STINGRAY'              => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'ANCIENT OF THE DEEP'   => array('10-15KM', '15-25KM', '25KM+'),
    'BARRACUDA'             => array('0-2KM'),
    'CARNIVORE'             => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'DERPSHARK'             => array('0-2KM', '2-10KM'),
    'SERPENT MONSTER'       => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'SPEAR FISHER'          => array('0-2KM', '2-10KM'),
    'TRITUS'                => array('10-15KM', '15-25KM', '25KM+'),
    'ANGLER'                => array('10-15KM', '15-25KM', '25KM+'),
    'GUPPY'                 => array('0-2KM', '2-10KM'),
    'MERMOUSETTE'           => array('2-10KM'),
    'MERSHARK'              => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'OCTOMERMAID'           => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'OLD ONE'               => array('10-15KM', '15-25KM', '25KM+'),
    'SCHOOL OF MISH'        => array('0-2KM'),
    'TADPOLE'               => array('0-2KM', '2-10KM'),
    'URCHIN KING'           => array('10-15KM', '15-25KM', '25KM+'),
    'PEARL'                 => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'PEARL DIVER'           => array('2-10KM', '10-15KM', '15-25KM', '25KM+'),
    'SALTWATER AXOLOTL'     => array('0-2KM', '2-10KM'),
    'SAND DOLLAR DIVER'     => array('0-2KM'),
    'SAND DOLLAR QUEEN'     => array('0-2KM', '2-10KM'),
    'TREASURE HOARDER'      => array('10-15KM', '15-25KM', '25KM+'),
    'TREASURE KEEPER'       => array('10-15KM', '15-25KM', '25KM+'),
);

// Zokor stages
static $zokor_mice_stages = array(
    'MUSH MONSTER'              => array('FARMING 0+', 'FARMING 50+'),
    'MUSHROOM HARVESTER'        => array('FARMING 0+', 'FARMING 50+'),
    'NIGHTSHADE FUNGALMANCER'   => array('FARMING 50+'),
    'NIGHTSHADE NANNY'          => array('FARMING 0+', 'FARMING 50+'),
    'SHADOW STALKER'            => array('FEALTY 15+', 'FEALTY 50+', 'FEALTY 80+', 'TECH 15+', 'TECH 50+', 'TECH 80+', 'SCHOLAR 15+', 'SCHOLAR 50+', 'SCHOLAR 80+', 'TREASURY 15+', 'TREASURY 50+', 'FARMING 0+', 'FARMING 50+', 'LAIR - EACH 30+'),
    'BATTLE CLERIC'             => array('FEALTY 15+', 'FEALTY 50+', 'FEALTY 80+'),
    'DARK TEMPLAR'              => array('FEALTY 80+'),
    'DRUDGE'                    => array('FEALTY 15+', 'FEALTY 50+', 'FEALTY 80+'),
    'MASKED PIKEMAN'            => array('FEALTY 15+', 'FEALTY 50+', 'FEALTY 80+'),
    'MIND TEARER'               => array('FEALTY 50+', 'FEALTY 80+'),
    'PALADIN WEAPON MASTER'     => array('FEALTY 80+'),
    'SIR FLEEKIO'               => array('FEALTY 50+', 'FEALTY 80+'),
    'SOLEMN SOLDIER'            => array('FEALTY 50+', 'FEALTY 80+'),
    'ANCIENT SCRIBE'            => array('SCHOLAR 50+', 'SCHOLAR 80+'),
    'ETHEREAL GUARDIAN'         => array('SCHOLAR 15+', 'SCHOLAR 50+', 'SCHOLAR 80+'),
    'MYSTIC GUARDIAN'           => array('SCHOLAR 50+', 'SCHOLAR 80+'),
    'MYSTIC HERALD'             => array('SCHOLAR 50+', 'SCHOLAR 80+'),
    'MYSTIC SCHOLAR'            => array('SCHOLAR 80+'),
    'SANGUINARIAN'              => array('SCHOLAR 15+', 'SCHOLAR 50+', 'SCHOLAR 80+'),
    'SOUL BINDER'               => array('SCHOLAR 80+'),
    'SUMMONING SCHOLAR'         => array('SCHOLAR 15+', 'SCHOLAR 50+', 'SCHOLAR 80+'),
    'ASH GOLEM'                 => array('TECH 15+', 'TECH 50+', 'TECH 80+'),
    'AUTOMATED STONE SENTRY'    => array('TECH 50+', 'TECH 80+'),
    'EXO-TECH'                  => array('TECH 15+', 'TECH 50+', 'TECH 80+'),
    'FUNGAL TECHNOMORPH'        => array('TECH 80+'),
    'MANAFORGE SMITH'           => array('TECH 80+'),
    'MATRON OF MACHINERY'       => array('TECH 50+', 'TECH 80+'),
    'RR-8'                      => array('TECH 15+', 'TECH 50+', 'TECH 80+'),
    'TECH GOLEM'                => array('TECH 50+', 'TECH 80+'),
    'HIRED EIDOLON'             => array('TREASURY 15+', 'TREASURY 50+'),
    'MATRON OF WEALTH'          => array('TREASURY 15+', 'TREASURY 50+'),
    'MIMIC'                     => array('TREASURY 15+', 'TREASURY 50+'),
    'MOLTEN MIDAS'              => array('TREASURY 50+'),
    'TREASURE BRAWLER'          => array('TREASURY 50+'),
    'CORRIDOR BRUISER'          => array('LAIR - EACH 30+'),
    'DECREPIT TENTACLE TERROR'  => array('LAIR - EACH 30+'),
    'RETIRED MINOTAUR'          => array('LAIR - EACH 30+'),
    'REANIMATED CARVER'         => array('FEALTY 15+', 'FEALTY 50+', 'FEALTY 80+', 'TECH 15+', 'TECH 50+', 'TECH 80+', 'SCHOLAR 15+', 'SCHOLAR 50+', 'SCHOLAR 80+', 'TREASURY 15+', 'TREASURY 50+', 'FARMING 0+', 'FARMING 50+', 'LAIR - EACH 30+'),
);

// Labyrinth stages
static $labyrinth_mice_stages = array(
    'ASH GOLEM'                 => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH'),
    'AUTOMATED STONE SENTRY'    => array('SUPERIOR TECH', 'EPIC TECH'),
    'CORRIDOR BRUISER'          => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH', 'PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY', 'PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR', 'PLAIN FARMING', 'SUPERIOR FARMING', 'PLAIN TREASURY', 'SUPERIOR TREASURY', 'INTERSECTIONS'),
    'DARK TEMPLAR'              => array('EPIC FEALTY'),
    'DRUDGE'                    => array('PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY'),
    'FUNGAL TECHNOMORPH'        => array('EPIC TECH'),
    'HIRED EIDOLON'             => array('PLAIN TREASURY', 'SUPERIOR TREASURY'),
    'LOST'                      => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH', 'PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY', 'PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR', 'PLAIN FARMING', 'SUPERIOR FARMING', 'PLAIN TREASURY', 'SUPERIOR TREASURY', 'INTERSECTIONS'),
    'LOST LEGIONNAIRE'          => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH', 'PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY', 'PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR', 'PLAIN FARMING', 'SUPERIOR FARMING', 'PLAIN TREASURY', 'SUPERIOR TREASURY', 'INTERSECTIONS'),
    'MASKED PIKEMAN'            => array('PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY'),
    'MIMIC'                     => array('PLAIN TREASURY', 'SUPERIOR TREASURY'),
    'MIND TEARER'               => array('SUPERIOR FEALTY', 'EPIC FEALTY'),
    'MUSH MONSTER'              => array('PLAIN FARMING', 'SUPERIOR FARMING'),
    'MUSHROOM HARVESTER'        => array('PLAIN FARMING', 'SUPERIOR FARMING'),
    'MYSTIC GUARDIAN'           => array('SUPERIOR SCHOLAR', 'EPIC SCHOLAR'),
    'MYSTIC HERALD'             => array('SUPERIOR SCHOLAR', 'EPIC SCHOLAR'),
    'MYSTIC SCHOLAR'            => array('EPIC SCHOLAR'),
    'NIGHTSHADE NANNY'          => array('SUPERIOR FARMING'),
    'REANIMATED CARVER'         => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH', 'PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY', 'PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR', 'PLAIN FARMING', 'SUPERIOR FARMING', 'PLAIN TREASURY', 'SUPERIOR TREASURY'),
    'RR-8'                      => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH'),
    'SANGUINARIAN'              => array('PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR'),
    'SHADOW STALKER'            => array('PLAIN TECH', 'SUPERIOR TECH', 'EPIC TECH', 'PLAIN FEALTY', 'SUPERIOR FEALTY', 'EPIC FEALTY', 'PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR', 'PLAIN FARMING', 'SUPERIOR FARMING', 'PLAIN TREASURY', 'SUPERIOR TREASURY', 'INTERSECTIONS'),
    'SOLEMN SOLDIER'            => array('SUPERIOR FEALTY', 'EPIC FEALTY'),
    'SUMMONING SCHOLAR'         => array('PLAIN SCHOLAR', 'SUPERIOR SCHOLAR', 'EPIC SCHOLAR'),
    'TECH GOLEM'                => array('SUPERIOR TECH', 'EPIC TECH'),
    'TREASURE BRAWLER'          => array('SUPERIOR TREASURY'),
);

// Burroughs Rift stages
static $burroughs_rift_mice_stages = array(
    'AMPLIFIED BROWN'                   => array('MIST 0'),
    'AMPLIFIED GREY'                    => array('MIST 0'),
    'AMPLIFIED WHITE'                   => array('MIST 0'),
    'ASSASSIN BEAST'                    => array('MIST 19-20'),
    'AUTOMATED SENTRY'                  => array('MIST 0'),
    'BIG BAD BEHEMOTH BURROUGHS'        => array('MIST 19-20'),
    'BOULDER BITER'                     => array('MIST 6-18'),
    'CLUMP'                             => array('MIST 1-5', 'MIST 6-18'),
    'COUNT VAMPIRE'                     => array('MIST 1-5', 'MIST 6-18'),
    'CYBER MINER'                       => array('MIST 1-5', 'MIST 6-18'),
    'CYBERNETIC SPECIALIST'             => array('MIST 0'),
    'DOKTOR'                            => array('MIST 0'),
    'EVIL SCIENTIST'                    => array('MIST 0'),
    'ITTY BITTY RIFTY BURROUGHS'        => array('MIST 1-5', 'MIST 6-18'),
    'LAMBENT'                           => array('MIST 6-18'),
    'LYCANOID'                          => array('MIST 6-18'),
    'MASTER EXPLODER'                   => array('MIST 6-18'),
    'MECHA TAIL'                        => array('MIST 1-5', 'MIST 6-18'),
    'MONSTROUS ABOMINATION'             => array('MIST 19-20'),
    'PHASE ZOMBIE'                      => array('MIST 1-5', 'MIST 6-18'),
    'PLUTONIUM TENTACLE'                => array('MIST 19-20'),
    'PNEUMATIC DIRT DISPLACEMENT'       => array('MIST 1-5', 'MIST 6-18'),
    'PORTABLE GENERATOR'                => array('MIST 0'),
    'PROTOTYPE'                         => array('MIST 1-5', 'MIST 6-18'),
    'RADIOACTIVE OOZE'                  => array('MIST 1-5', 'MIST 6-18'),
    'RANCID BOG BEAST'                  => array('MIST 6-18', 'MIST 19-20'),
    'REVENANT'                          => array('MIST 6-18'),
    'RIFT BIO ENGINEER'                 => array('MIST 0'),
    'RIFTERRANIAN'                      => array('MIST 1-5', 'MIST 6-18'),
    'ROBAT'                             => array('MIST 1-5', 'MIST 6-18'),
    'SUPER MEGA MECHA ULTRA ROBOGOLD'   => array('MIST 6-18', 'MIST 19-20'),
    'SURGEON BOT'                       => array('MIST 0'),
    'TECH RAVENOUS ZOMBIE'              => array('MIST 1-5', 'MIST 6-18'),
    'THE MENACE OF THE RIFT'            => array('MIST 19-20'),
    'TOXIC AVENGER'                     => array('MIST 6-18', 'MIST 19-20'),
    'TOXIKINETIC'                       => array('MIST 1-5', 'MIST 6-18'),
    'ZOMBOT UNIPIRE THE THIRD'          => array('MIST 6-18')
);


// Whisker Woods Rift stages
static $whisker_woods_rift_mice_stages = array(
    'BLOOMED SYLVAN'        => array('CC 0-24',                                                                                                 ),
    'CENTAUR RANGER'        => array('CC 0-24', 'CC 25-49', 'CC 50',                                'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'CHERRY SPRITE'         => array('CC 0-24', 'CC 25-49', 'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'CRANKY CATERPILLAR'    => array('CC 0-24',                                                                                                 ),
    'CRAZED GOBLIN'         => array(                                                                           'DL 0-24'                       ),
    'CYCLOPS BARBARIAN'     => array(                       'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'FUNGAL FROG'           => array(                                   'GGT 0-24',                                                             ),
    'GILDED LEAF'           => array('CC 0-24', 'CC 25-49', 'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'GRIZZLED SILTH'        => array('CC 0-24', 'CC 25-49', 'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'KARMACHAMELEON'        => array(                                   'GGT 0-24',                                                             ),
    'MEDICINE'              => array(                                                                                       'DL 25-49'          ),
    'MONSTROUS BLACK WIDOW' => array(           'CC 25-49', 'CC 50',                'GGT 25-49',    'GGT 50',               'DL 25-49', 'DL 50' ),
    'MOSSY MOOSKER'         => array('CC 0-24',                                                                                                 ),
    'NATURALIST'            => array('CC 0-24', 'CC 25-49', 'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',   'DL 0-24',  'DL 25-49', 'DL 50' ),
    'NOMADIC WARRIOR'       => array(                                               'GGT 25-49',                                                ),
    'RED COAT BEAR'         => array(                                               'GGT 25-49',                                                ),
    'RED-EYED WATCHER OWL'  => array(           'CC 25-49',                                                                                     ),
    'RIFT TIGER'            => array(                                               'GGT 25-49',                                                ),
    'SPIRIT FOX'            => array(           'CC 25-49',                                                                                     ),
    'SPIRIT OF BALANCE'     => array(                                   'GGT 0-24',                                                             ),
    'TREANT QUEEN'          => array(           'CC 25-49',                                                                                     ),
    'TREE TROLL'            => array(                                                                                       'DL 25-49',         ),
    'TRI-DRA'               => array('CC 0-24', 'CC 25-49', 'CC 50',    'GGT 0-24', 'GGT 25-49',    'GGT 50',                           'DL 50' ),
    'TWISTED TREANT'        => array(                                                                           'DL 0-24',                      ),
    'WATER SPRITE'          => array(                                                                           'DL 0-24',                      ),
    'WINGED HARPY'          => array(                                                                                       'DL 25-49',         ),
);

// Toxic Spill stages
static $spill_mice_stages = array(
    'BIOHAZARD'             => array('COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'BOG BEAST'             => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'GELATINOUS OCTAHEDRON' => array('KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'HAZMAT'                => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'LAB TECHNICIAN'        => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'MONSTER TAIL'          => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'MUTANT MONGREL'        => array('DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'MUTANT NINJA'          => array('COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'MUTATED BEHEMOTH'      => array('ARCHDUKE / ARCHDUCHESS'),
    'MUTATED SIBLINGS'      => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'OUTBREAK ASSASSIN'     => array('DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'PLAGUE HAG'            => array('LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'SCRAP METAL MONSTER'   => array('LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'SLIMEFIST'             => array('DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'SLUDGE'                => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'SLUDGE SOAKER'         => array('LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'SLUDGE SWIMMER'        => array('GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'SPORE'                 => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'SWAMP RUNNER'          => array('HERO', 'KNIGHT', 'LORD / LADY', 'BARON / BARONESS', 'COUNT / COUNTESS', 'DUKE / DUCHESS'),
    'TELEKINETIC MUTANT'    => array('COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'TENTACLE'              => array('COUNT / COUNTESS', 'DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'THE MENACE'            => array('GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
    'TOXIC WARRIOR'         => array('DUKE / DUCHESS', 'GRAND DUKE / GRAND DUCHESS', 'ARCHDUKE / ARCHDUCHESS'),
);

// This importer takes a csv with 3 columns: 1. Mice 2. Locations 3. Cheeses
while (!feof($file)) {
    $num_rows++;
    $row = fgetcsv($file);

    //    print "Processing file row $num_rows...\n";
    // $row[0] is mouse name, row[1] are locations, row[2] are cheeses

    // Mice
    $mouse = strtoupper(trim($row[0]));

    if (empty($mouse)) continue;

    $mouse = str_replace(' MOUSE', '', $mouse);

    // Check if mouse already exists, then skip it
    try {
        $result = $db->prepare("
            SELECT COUNT(*)
            FROM mice m
            WHERE m.name LIKE ?");
        $result->execute(array($mouse));
    }
    catch(PDOException $ex) {
        error_log($ex->getMessage());
    }
    $number_of_rows = $result->fetchColumn();

    if ($number_of_rows > 0) {
        print "Skipping existing mouse: $mouse\n";
        continue;
    }

    //    print('Adding new mouse: ' . $mouse . "\n");

    //insert mouse into mice table
    try {
        $result = $db->prepare("INSERT IGNORE INTO `mice`(`name`) VALUES (?)");
        $result->execute(array($mouse));
    }
    catch(PDOException $ex) {
        error_log($ex->getMessage());
    }

    // Locations
    $row[1] = strtoupper($row[1]);
    $locations = explode("||", $row[1]);
    $locations = array_map('trim', $locations);

    foreach ($locations as $location) {
        if (empty($location)) continue;
        $stage = array('');

        if (preg_match('/^MANY\sLOCATIONS/', $location)
            || preg_match('/^ALL\sLOCATIONS\sEXCEPT/', $location)
            || preg_match('/^ALL\sEXCEPT/', $location)
            ) $location = 'SEE WIKI';
        else if (preg_match('/^CALAMITY\sCARL/', $location)) $location = "CALAMITY CARL'S CRUISE";
        else if (preg_match('/^FIERY\sWARPATH/', $location)) {
            list($location, $stage[0]) = explode("--", $location);
            if ($stage[0] == 'WAVES 1-3') {
                $stage[0] = 'WAVE 1';
                $stage[1] = 'WAVE 2';
                $stage[2] = 'WAVE 3';
            }
        }
        else if (preg_match('/^BURROUGHS\sRIFT/', $location)) {
            if (array_key_exists($mouse, $burroughs_rift_mice_stages)) {
                foreach($burroughs_rift_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^WHISKER\sWOODS\sRIFT/', $location)) {
            if (array_key_exists($mouse, $whisker_woods_rift_mice_stages)) {
                foreach($whisker_woods_rift_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^LABYRINTH/', $location)) {
            if (array_key_exists($mouse, $labyrinth_mice_stages)) {
                foreach($labyrinth_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^ZOKOR/', $location)) {
            if (array_key_exists($mouse, $zokor_mice_stages)) {
                foreach($zokor_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^SUNKEN\sCITY/', $location)) {
            if (array_key_exists($mouse, $sunken_city_mice_stages)) {
                foreach($sunken_city_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^GNAWNIAN\sEXPRESS\sSTATION/', $location)) {
            if (array_key_exists($mouse, $gnawnian_express_mice_stages)) {
                foreach($gnawnian_express_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^BALACK\'S\sCOVE/', $location)) {
            if (array_key_exists($mouse, $balacks_cove_mice_stages)) {
                foreach($balacks_cove_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^ICEBERG/', $location)) {
            if (array_key_exists($mouse, $iceberg_mice_stages)) {
                foreach($iceberg_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }
        else if (preg_match('/^TOXIC\sSPILL/', $location)) {
            if (array_key_exists($mouse, $spill_mice_stages)) {
                foreach($spill_mice_stages[$mouse] as $id => $stg) {
                    $stage[$id] = $stg;
                }
            }
        }

        foreach ($stage as $st) {

            //insert ignore location into locations table
            try {
                $result = $db->prepare("INSERT IGNORE INTO `locations`(`name`, `stage`) VALUES (?, ?)");
                $result->execute(array($location, $st));
            }
            catch(PDOException $ex) {
                print ($ex->getMessage());
            }

            //insert ignore mice.id, locations.id into mice_locations where mice.name = $mouse and locations.name = $location
            try {
                $query = "
                    INSERT IGNORE INTO `mice_locations` (`mice_id`, `locations_id`)
                    SELECT m.id, l.id
                    FROM mice m
                    INNER JOIN locations l ON l.name = ?
                    WHERE m.name = ?";
                $query_params = array($location, $mouse);
                if ($st != '') {
                    $query.= ' AND l.stage = ?';
                    $query_params[] = $st;
                }
                $result = $db->prepare($query);
                $result->execute($query_params);
            }
            catch(PDOException $ex) {
                print ($ex->getMessage());
            }
        }
    }

    // Cheese
    if (!array_key_exists(2, $row) || $row[2] == '') {
        $row[2] = 'REGULAR CHEESE';
    }

    //  print "Processing $row[2] cheese\n";
    $row[2] = strtoupper($row[2]);
    $cheeses = explode("||", $row[2]);
    $cheeses = array_map('trim', $cheeses);

    $cheese_count = 0;
    foreach ($cheeses as $cheese) {
        if (empty($cheese)) {
            if ($cheese_count == 0) $cheese = 'REGULAR CHEESE';
            else continue;
        }

        //insert ignore cheese into cheeses table
        try {
            $result = $db->prepare("INSERT IGNORE INTO `cheeses`(`name`) VALUES (?)");
            $result->execute(array($cheese));
        }
        catch(PDOException $ex) {
            error_log($ex->getMessage());
        }

        //insert ignore mice.id, cheese.id into mice_cheeses where mice.name = $mouse and cheese.name = $cheese
        try {
            $result = $db->prepare("
                INSERT IGNORE INTO `mice_cheeses` (`mice_id`, `cheeses_id`)
                SELECT m.id, c.id
                FROM mice m
                INNER JOIN cheeses c ON c.name = ?
                WHERE m.name = ?
            ");
            $result->execute(array($cheese, $mouse));
        }
        catch(PDOException $ex) {
            error_log($ex->getMessage());
        }
        $cheese_count++;
    }
}

fclose($file);
print "Processed $num_rows\n";
print "Done!! Wooot! :)\n";
?>

