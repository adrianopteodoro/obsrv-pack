# DNASrep
DNAS replacement server

This is a small setup to run your own DNAS service for Playstation 2. The scripts basically replay packets that were captured by the DNAS forever project.

MCrypt has been removed with PHP 7.2. This version of DNASrep takes care of this problem by using OpenSSL instead.

After more investigations I added folder "more" with scripts, examples and notes. It should provide a good base for people who want to emulate more functions aof the original DNAS server infrastructure like banning mechanisms or title control. It also holds the prove that it's theoretically possible to authenticate titles that weren't captured by the DNASrep project back in the days.

I merged the pull request for PSBBN support from the old DNASrep repository on github. Be aware that I haven't checked the functionality!


## What Is Supported?

### Original PS2 games and software 
PS2 DNAS-net disk based authentication for multiple regions is supported as long as the needed packets were captured by the DNAS forever project. This is the complete list. I'm not sure if the captures for Nobunaga's ambittion online are included, so I put them into brackets.


| gamename | EU | JP | US |
| ------ | ------ | ------ | ------ |
| 187 Ride Or Die	| X |   |   |
| ATV Offroad Fury 3 |   |   | X |
| ATV Offroad Fury 4 |   |   | X |
| AllStar Baseball 2005 |   |   | X |
| Area 51	| X |   |   |	
| Battlefield 2 Modern Combat | X | X | X |
| Burnout 3 Takedown | X |   | X |
| Burnout Revenge | X |   | X |
| Call of Duty 2 Big Red One | X |   | X |
| Call of Duty 3 | X |   | X |
| Call of Duty Finest Hour | X |   | X |
| Champions Return to Arms |   |   | X |
| Champions of Norrath |   |   | X |
| Cold Winter |   |   | X |
| Commandos Strike Force |   |   | X |
| Culdcept II |   | X |   |
| Deer Hunter |   |   | X |
| Destruction Derby Arenas |   |   | X |
| Everquest Online Adventures | X |   |   |
| FIFA07 | X |   |   |		
| FlatOut 2 |   |   | X |
| Gauntlet Seven Sorrows |  |   | X |
| Greg Hastings Tournament Paintball MAXD |   |   | X |
| Gundam vs ZGundam |   | X  |   |
| Hardware Online Arena | X |   |   |		
| Hot Wheels Stunt Track Challenge |   |   | X |
| JAK X Combat Racing |   |   | X |
| Jak X | X |   |   |
| KILLZONE | X |   | X |
| KOF MAXIMUM IMPACT REGULATION A |   | X |   |
| KOF Maximum Impact 2 |   | X |   |
| Lemmings | X |   |   |
| MADDEN NFL 07 |   |   | X |
| MLB 06 The Show |   |   | X |
| Madden NFL 2004	 |   |   | X |
| Medal of Honor Rising Sun |   |   | X |
| Medal of Honor Soleil levant | X |   |   |	
| Metal Gear Solid 3 Snake Eater | X |   |   |	
| Metal Gear Solid 3 Disc 2 Persistence | X |   |   |	
| Midnight Club 3 DUB Edition Remix |   |   | X |
| Mobile Suit Z Gundam AEUG vs Titans |   | X |   |
| Monster Hunter | X | X | X |
| Monster Hunter G |   | X |   |
| Monster Hunter dos |   | X |   |
| Mortal Kombat Armageddon |   |   | X |
| NASCAR 07 |   |   | X |
| NASCAR Thunder 2004 |   |   | X |
| NBA Ballers |   |   | X |
| NBA Ballers Phenom |   |   | X |
| NFL Street 2 |   |   | X |
| NHL 2K6 | X |   |   |			
| Need for Speed Underground | X |   | X |
| Need for Speed Underground 2 | X |   | X |
| NeoGeo Battle Coliseum |   | X |   |
| Network Adaptor StartUp Disc V2 |   |   | X |
| Network Adaptor StartUp Disc V25 |   |   | X |
| Network Start Up Disc v4 |   |   | X |
| (Nobunagas Ambition Online Installation) |   | X |   |
| (Nobunagas Ambition Online installed) |   | X |   |
| PES6 SLES54203 | X |   |   |		
| Phantasy Star Universe | X | X | X |
| Phantasy Star Universe Ambition of the Illuminus | X | X | X |
| Phantasy Star Universe Premiere Disc |   | X |   |
| PlayStation BB Navigator 032 |   | X |   |	
| Pro Evolution Soccer 6 SLES 54360 | X |   |   |	
| Pro Evolution Soccer 6 SLES 54360 | X |   |   |		
| Project Snowblind | X |   |   |		
| Ratchet Clank Up Your Arsenal |   |   | X |
| Ratchet Deadlocked |   |   | X |
| Resident Evil Outbreak File 1 |   | X | X |
| Resident Evil Outbreak File 2 | X | X | X |
| Risk Global Domination |   |   | X |
| Robotech Invasion |   |   | X |
| Rogue Trooper |   |   | X |
| SAMURAI SHODOWN VI |   | X |   |	
| Sniper Elite |   |   | X |
| Socom II US NAVY SEALs | X |   | X |		
| Socom III US NAVY SEALs | X |   | X |
| Socom US NAVY SEALs | X |   |   |	
| Socom US NAVY SEALs Combined Assault | X |   | X |
| Splinter Cell Double agent | X |   |   |	
| Splinter Cell Pandora Tomorrow | X |   |   |	
| Stacked with Daniel Negreanu |   |   | X |	
| Star Wars Battlefront | X | X | X |
| Star Wars Battlefront II | X | X | X |
| Syphon Filter The Omega Strain | X |   | X |
| Test Drive Unlimited | X |   |   |
| The King of Fighters Neowave |   | X |   |
| The King of Fighters XI |   | X |   |
| The Sims Busting Out |   |   | X |
| TimeSplitters Future Perfect | X |   | X |
| ToCA Race Driver 3 |   |   | X |
| Tom Clancys Ghost Recon Advance Warfighter | X |   |   |
| Tony Hawks American Wasteland |   |   | X |
| Tony Hawks Underground |   |   | X |
| Tony Hawks Underground 2 |   |   | X |
| Urban Chaos Riot Response |   |   | X |
| WRC Avec Sebastien Loeb Edition 2005 | X |   |   |		
| WRC Rally Evolved | X |   |   |		
| Warhammer 40000 Fire Warrior | X |   |   |		
| Winning Eleven 9 |   | X |   |
| X Men Legends II Rise of Apocalypse |   |   | X |
| XIII |   |   | X |
| hack fragment |   | X |   |


### PS2BBN preservation of update files

For preservation purposes support for the authored DNAS update files and XML files that are responsible for making BBN work is added. Many of the linked destinations do not exist anymore, so this is a possible field of research and support.

Note: You will need a legitimate copy of the install disc and/or the PS2HDD with an activated BBN installation.


# Credits

## Participants of the original DNAS forever project
 FuryK96
 Viscosity 
 DARKFORCE 
 ResistantFTW 
 mecha 
 Hunk91 
 shade
 Richi902
 Anomaladox 
 DonkeyKong
 Gandi

## PSBNN enhancements
 Based-Skid

And anyone else involved with the project not otherwise mentioned.

# Disclaimer
The PS2 console and DNAS/PS2BBN software were created Sony Computer Entertainment. This project is not affiliated wth Sony.

DNASrep is meant for use with authentic and licensed Playstation 2 hardware and software. It should not be used to attempt to facilitate copyright infringment.
The developers and authors of this Software cannot be held liable for your use or misuse of this software.
