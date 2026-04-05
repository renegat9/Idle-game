# 🌳 Arbres de Talents v2 — Les 8 Classes

## Règles générales

| Paramètre | Valeur |
|-----------|--------|
| `TALENT_POINTS_INTERVAL` | 5 (1 point tous les 5 niveaux) |
| `TALENT_POINTS_MAX` | 20 (au niveau 100) |
| `TALENTS_PER_BRANCH` | 7 |
| `BRANCHES_PER_CLASS` | 3 |
| `TOTAL_TALENTS_PER_CLASS` | 21 |
| `TALENT_COST_MIN` | 1 |
| `TALENT_COST_MAX` | 3 |
| `TALENT_RESET_BASE_COST` | 500 (or) |
| `TALENT_RESET_COST_MULTIPLIER` | 150 (% du coût précédent) |

### Structure des paliers

| Palier | Points investis requis dans la branche | Talents | Coûts typiques |
|--------|----------------------------------------|---------|----------------|
| Palier 1 | 0 | Talents 1, 2 | 1-2 chacun |
| Palier 2 | 3 | Talents 3, 4 | 1-2 chacun |
| Palier 3 | 6 | Talents 5, 6, 7 (capstone) | 1-2 + capstone à 3 |

### Coût total par branche

Chaque branche coûte **12-14 points** pour tout débloquer. Avec 20 points max, un joueur peut :
- Maxer 1 branche (12-14) + investir partiellement dans une autre (6-8)
- Ou répartir sur 2 branches sans les maxer
- **Impossible** de maxer 2 branches complètes (24-28 > 20)

### Types de talents

- **Passif (P) :** Bonus permanent
- **Actif (A) :** Compétence avec cooldown
- **Réactif (R) :** Se déclenche automatiquement sous condition

---

## 1. GUERRIER

*"Se battre c'est bien. Comprendre pourquoi, c'est optionnel."*

### Branche A — Rempart (Tank)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Peau Épaisse | P | 1 | 1 | DEF +10% |
| 2 | Provocation | A | 2 | 1 | Force tous les ennemis à cibler le Guerrier pendant 2 tours. CD : 5 tours |
| 3 | Endurance | P | 1 | 2 | PV max +15% |
| 4 | Mur de Bouclier | R | 2 | 2 | Quand un allié tombe sous 20% PV, le Guerrier absorbe 50% des dégâts suivants à sa place pendant 1 tour |
| 5 | Représailles | R | 2 | 3 | Chaque fois que le Guerrier est touché, riposte pour 30% de son ATQ |
| 6 | Cri Intimidant | A | 2 | 3 | Tous les ennemis ont -20% ATQ pendant 3 tours. CD : 6 tours |
| 7 | **Forteresse Vivante** | P | 3 | 3 | DEF +25%. Quand le Guerrier est au-dessus de 50% PV, toute l'équipe gagne +10% DEF |

### Branche B — Bras Armé (DPS)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Force Brute | P | 1 | 1 | ATQ +10% |
| 2 | Coup Puissant | A | 2 | 1 | Attaque à 180% de l'ATQ. CD : 3 tours |
| 3 | Entraînement | P | 1 | 2 | Chance de critique +8% |
| 4 | Exécution | R | 2 | 2 | Si la cible est sous 25% PV, les dégâts sont doublés |
| 5 | Frénésie | P | 2 | 3 | Chaque kill donne +10% ATQ cumulable, max 5 stacks. Reset chaque combat |
| 6 | Charge Dévastatrice | A | 2 | 3 | Attaque tous les ennemis à 80% de l'ATQ. CD : 5 tours |
| 7 | **Maître d'Armes** | P | 3 | 3 | ATQ +20%. Les critiques infligent 200% au lieu de 150% |

### Branche C — Calamité (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Maladresse Calculée | P | 1 | 1 | Quand un trait négatif se déclenche, le Guerrier gagne +15% ATQ pendant 2 tours |
| 2 | Casse-Tout | R | 2 | 1 | 20% de chance de détruire l'arme de l'ennemi (ATQ ennemi -30% pour le combat) |
| 3 | Dommages Collatéraux | P | 2 | 2 | Quand le Guerrier rate (esquive ennemi), inflige 50% des dégâts à un ennemi adjacent aléatoire |
| 4 | Tête Dure | P | 1 | 2 | Les effets de statut négatifs durent 1 tour de moins sur le Guerrier |
| 5 | Chaos Tactique | R | 2 | 3 | Quand le trait se déclenche 2 fois dans le même combat, le Guerrier entre en Rage : +30% ATQ et VIT pendant 3 tours |
| 6 | Fier Incompétent | P | 2 | 3 | Le % de déclenchement du trait est réduit de 5 points mais chaque déclenchement donne +5% de toutes les stats pour le reste du combat |
| 7 | **Catastrophe Ambulante** | P | 3 | 3 | Les effets négatifs du trait affectent AUSSI l'ennemi le plus proche. Couard qui fuit → un ennemi fuit aussi. Narcoleptique → un ennemi s'endort aussi |

---

## 2. MAGE

*"La magie, c'est comme la cuisine : parfois ça explose."*

### Branche A — Élémentaliste (DPS mono-cible)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Concentration | P | 1 | 1 | INT +10% |
| 2 | Trait de Feu | A | 2 | 1 | Dégâts magiques à 160% de l'INT sur une cible. CD : 3 tours |
| 3 | Pénétration Arcanique | P | 2 | 2 | Les sorts ignorent 20% supplémentaires de la résistance magique |
| 4 | Résonance | R | 1 | 2 | Si le sort tue la cible, 40% des dégâts excédentaires se propagent à un autre ennemi |
| 5 | Canalisation | A | 2 | 3 | Skip un tour pour doubler les dégâts du prochain sort. CD : 4 tours |
| 6 | Embrasement | P | 2 | 3 | Les sorts ont 25% de chance d'infliger "En feu" (3 tours) |
| 7 | **Archimage** | P | 3 | 3 | INT +20%. Les sorts critiques lancent automatiquement un second sort gratuit à 50% des dégâts |

### Branche B — Arcaniste (AoE / Contrôle)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Onde de Choc | A | 2 | 1 | Dégâts magiques à 70% de l'INT sur tous les ennemis. CD : 4 tours |
| 2 | Gel | A | 1 | 1 | Ralentit une cible pendant 2 tours. CD : 3 tours |
| 3 | Tempête Arcanique | P | 2 | 2 | Les AoE gagnent +15% de dégâts |
| 4 | Chaîne d'Éclairs | A | 1 | 2 | Touche 3 ennemis à 60% puis 40% puis 20% de l'INT. CD : 5 tours |
| 5 | Silence | A | 2 | 3 | Empêche un ennemi d'utiliser des compétences pendant 2 tours. CD : 6 tours |
| 6 | Bouclier de Mana | R | 2 | 3 | Quand le Mage tombe sous 30% PV, gagne un bouclier absorbant égal à 50% de son INT pendant 2 tours |
| 7 | **Maître du Chaos** | P | 3 | 3 | Les AoE ont 15% de chance de déclencher un effet aléatoire : Étourdi, Ralenti, En feu, ou Terrifié |

### Branche C — Instabilité (Branche du Défaut)
*Coût total : 12 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Magie Instable | P | 1 | 1 | Les sorts ont une variance élargie : 70-140% au lieu de 90-110% |
| 2 | Siphon d'Erreur | R | 2 | 1 | Quand un trait négatif se déclenche, le Mage récupère 10% de ses PV max |
| 3 | Explosion Involontaire | R | 2 | 2 | Quand le Mage est étourdi ou endormi par son trait, une explosion inflige 80% de l'INT en dégâts à tous les ennemis |
| 4 | Distorsion Réelle | P | 1 | 2 | 10% de chance que les dégâts reçus soient redirigés vers un ennemi aléatoire |
| 5 | Faille Temporelle | A | 2 | 3 | Rejoue le dernier tour du Mage (même sort, même cible). CD : 8 tours |
| 6 | Éruption de Trait | P | 1 | 3 | Le trait a +10% de chance de se déclencher, mais chaque déclenchement augmente l'INT de 8% pour le combat |
| 7 | **Bombe à Retardement** | R | 3 | 3 | Quand le Mage meurt, explose : 200% INT en dégâts à tous les ennemis. Si ça tue au moins un ennemi, le Mage revient avec 1 PV |

---

## 3. VOLEUR

*"C'est pas du vol, c'est de la redistribution non consentie."*

### Branche A — Assassin (DPS burst)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Lames Aiguisées | P | 1 | 1 | ATQ +10% |
| 2 | Embuscade | R | 2 | 1 | La première attaque du combat inflige 200% des dégâts |
| 3 | Points Vitaux | P | 1 | 2 | Chance de critique +10% |
| 4 | Poison | A | 2 | 2 | La prochaine attaque empoisonne (3% PV max/tour pendant 4 tours). CD : 5 tours |
| 5 | Ombre Mortelle | A | 2 | 3 | Invisible 1 tour (non ciblable). Prochaine attaque à 250%. CD : 6 tours |
| 6 | Hémorragie | P | 2 | 3 | Les critiques infligent un saignement : 4% PV max/tour pendant 3 tours |
| 7 | **Coup Fatal** | R | 3 | 3 | Les attaques contre un ennemi sous 15% PV sont des kills automatiques. Ne fonctionne pas sur les boss |

### Branche B — Ombre (Esquive / Utilitaire)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Agilité | P | 1 | 1 | VIT +15% |
| 2 | Évasion | P | 2 | 1 | Esquive +8% |
| 3 | Feinte | A | 2 | 2 | L'ennemi ciblé rate automatiquement sa prochaine attaque. CD : 4 tours |
| 4 | Pas de l'Ombre | R | 1 | 2 | Après une esquive, +30% ATQ sur la prochaine attaque |
| 5 | Double Lame | P | 2 | 3 | Le Voleur attaque deux fois par tour, la seconde à 50% des dégâts |
| 6 | Piège | A | 2 | 3 | Piège invisible. Le prochain attaquant est étourdi 1 tour + prend 100% ATQ. CD : 6 tours |
| 7 | **Fantôme** | P | 3 | 3 | Esquive +15%. Chaque esquive a 30% de chance de rendre le Voleur invisible 1 tour |

### Branche C — Filou (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Doigts Agiles | P | 2 | 1 | +15% de chance de loot supplémentaire après chaque combat |
| 2 | Pickpocket | R | 1 | 1 | Quand le trait se déclenche, vole un buff de l'ennemi au lieu de subir le malus |
| 3 | Coup Bas | P | 2 | 2 | Si le Voleur agit en dernier dans un tour, ses dégâts sont +40% |
| 4 | Échappatoire | R | 1 | 2 | Si le Voleur fuit (Couard ou autre), il vole 1 objet à l'ennemi en partant |
| 5 | Baratineur | P | 2 | 3 | 10% de chance qu'un ennemi hésite et skip son tour |
| 6 | Sabotage Discret | A | 2 | 3 | -30% DEF d'un ennemi pendant 3 tours. CD : 5 tours |
| 7 | **Roi des Embrouilles** | R | 3 | 3 | Quand le trait se déclenche, un effet positif aléatoire se produit en plus : loot bonus, buff d'équipe, debuff ennemi, ou soin à 20% PV max |

---

## 4. RANGER

*"Je ne rate jamais ma cible. C'est la cible qui esquive."*

### Branche A — Tireur d'Élite (DPS mono-cible)
*Coût total : 14 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Visée Stable | P | 1 | 1 | ATQ +10% |
| 2 | Tir Précis | A | 2 | 1 | Attaque à 170% ATQ, ne peut pas être esquivée. CD : 3 tours |
| 3 | Point Faible | P | 2 | 2 | Les critiques ignorent 30% de la DEF de la cible |
| 4 | Tir Perforant | A | 1 | 2 | Traverse la cible et touche l'ennemi derrière à 60%. CD : 4 tours |
| 5 | Concentration Absolue | P | 2 | 3 | Si le Ranger n'est pas touché pendant un tour, prochain tir +50% |
| 6 | Marque du Chasseur | A | 3 | 3 | Marque un ennemi : toute l'équipe fait +20% sur cette cible pendant 3 tours. CD : 6 tours |
| 7 | **Oeil de Faucon** | P | 3 | 3 | Critique +15%. Les critiques du Ranger infligent 250% au lieu de 150% |

### Branche B — Survivaliste (Hybride)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Peau Tannée | P | 1 | 1 | DEF +10%, PV +5% |
| 2 | Piège à Ours | A | 2 | 1 | Le prochain attaquant au corps à corps est immobilisé 2 tours. CD : 5 tours |
| 3 | Herboristerie | P | 1 | 2 | Les potions du Ranger soignent 30% de plus |
| 4 | Compagnon Faucon | P | 2 | 2 | Un faucon attaque chaque tour pour 20% ATQ du Ranger |
| 5 | Terrain Connu | P | 2 | 3 | +10% à toutes les stats dans les zones déjà complétées |
| 6 | Pluie de Flèches | A | 2 | 3 | Touche tous les ennemis à 60% ATQ + Ralenti 1 tour. CD : 6 tours |
| 7 | **Seigneur des Bêtes** | P | 3 | 3 | Le faucon devient un loup : 40% ATQ/tour + provoque l'attaquant d'un allié sous 20% PV pendant 1 tour |

### Branche C — Distrait (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Tir Chanceux | R | 2 | 1 | Quand le Ranger rate, 25% de chance de toucher un autre ennemi à plein dégâts |
| 2 | Instinct | P | 1 | 1 | Quand le trait se déclenche, +20% VIT pendant 2 tours |
| 3 | Rebond | P | 2 | 2 | Les tirs ratés ont 15% de chance de ricocher à 70% des dégâts |
| 4 | Observation Passive | P | 1 | 2 | Quand le Ranger skip un tour (trait), prochain tir +40% |
| 5 | Tir dans le Noir | A | 2 | 3 | Cible aléatoire à 200% ATQ. CD : 4 tours |
| 6 | Flèche Égarée | R | 2 | 3 | 10% de chance de tir gratuit supplémentaire sur une cible aléatoire à 60% ATQ |
| 7 | **Sniper Somnambule** | R | 3 | 3 | Les effets négatifs du trait déclenchent un tir automatique à 100% ATQ sur l'ennemi le plus faible AVANT de s'appliquer |

---

## 5. PRÊTRE

*"Je soigne par la foi. Et parfois par accident."*

### Branche A — Guérisseur (Soins)
*Coût total : 14 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Bénédiction | P | 1 | 1 | Soins +15% |
| 2 | Prière Rapide | A | 2 | 1 | Soigne un allié de 120% de l'INT. CD : 2 tours |
| 3 | Cercle de Soin | A | 2 | 2 | Soigne toute l'équipe de 50% INT. CD : 5 tours |
| 4 | Bouclier Sacré | A | 1 | 2 | Un allié gagne un bouclier de 40% INT pendant 3 tours. CD : 4 tours |
| 5 | Résurrection | A | 2 | 3 | Ranime un allié K.O. avec 30% PV max. 1 fois par combat |
| 6 | Aura Sacrée | P | 3 | 3 | Tous les alliés récupèrent 3% PV max par tour |
| 7 | **Saint Patron** | R | 3 | 3 | Quand un allié tomberait à 0 PV, le maintient à 1 PV (1 fois par combat par allié). Soins +20% |

### Branche B — Inquisiteur (DPS sacré)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Châtiment | A | 2 | 1 | Dégâts sacrés à 130% INT. CD : 2 tours |
| 2 | Ferveur | P | 1 | 1 | INT +10% |
| 3 | Marque Sacrée | A | 2 | 2 | L'ennemi ciblé prend +15% de dégâts de toutes sources pendant 3 tours. CD : 5 tours |
| 4 | Purification | A | 1 | 2 | Retire les effets négatifs d'un allié ET inflige les dégâts retirés à l'ennemi le plus proche. CD : 4 tours |
| 5 | Jugement | A | 2 | 3 | Dégâts = différence entre PV max et PV actuels de la cible. CD : 7 tours |
| 6 | Fanatisme | P | 2 | 3 | Les attaques sacrées soignent le Prêtre de 20% des dégâts infligés |
| 7 | **Fléau Divin** | P | 3 | 3 | Les sorts font dégâts ET soignent l'allié le plus faible de 50% des dégâts infligés |

### Branche C — Foi Vacillante (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Prière Confuse | R | 2 | 1 | Quand le trait se déclenche pendant un soin, le soin échoue mais inflige les dégâts équivalents à un ennemi |
| 2 | Martyr | P | 1 | 1 | +5% de toutes les stats pour chaque allié K.O. |
| 3 | Foi Aveugle | P | 1 | 2 | Les soins ciblent un allié aléatoire mais sont 30% plus puissants |
| 4 | Crise Mystique | R | 2 | 2 | Quand le trait se déclenche, 50% de chance de "Révélation" : prochain sort sans cooldown |
| 5 | Doute Existentiel | P | 2 | 3 | Trait -5% de déclenchement. Quand il se déclenche, toute l'équipe gagne +10% résistance pendant 2 tours |
| 6 | Miracle Involontaire | R | 2 | 3 | 5% de chance par tour de lancer un sort gratuit aléatoire |
| 7 | **Hérétique Sacré** | R | 3 | 3 | Le trait donne Inspiré (+20% ATQ/INT) à toute l'équipe quand il se déclenche |

---

## 6. BARDE

*"Ma musique inspire les foules. Enfin, elle les fait fuir, mais c'est une forme d'inspiration."*

### Branche A — Virtuose (Buffs d'équipe)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Mélodie Entraînante | P | 1 | 1 | Les buffs du Barde durent 1 tour de plus |
| 2 | Hymne de Guerre | A | 2 | 1 | Toute l'équipe +15% ATQ pendant 3 tours. CD : 5 tours |
| 3 | Ballade Protectrice | A | 2 | 2 | Toute l'équipe +15% DEF pendant 3 tours. CD : 5 tours |
| 4 | Tempo | P | 1 | 2 | Toute l'équipe +10% VIT permanent |
| 5 | Rappel Héroïque | A | 2 | 3 | Réinitialise le cooldown d'une compétence d'un allié. CD : 8 tours |
| 6 | Symphonie | P | 2 | 3 | Si le Barde buff 3 alliés dans le même combat, tous les buffs +10% efficacité |
| 7 | **Maestro** | P | 3 | 3 | Le Barde joue automatiquement un buff aléatoire gratuit chaque tour (ATQ, DEF, VIT, ou soin 5% PV max) |

### Branche B — Provocateur (Debuffs ennemis)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Insulte Cinglante | A | 2 | 1 | Une cible -15% ATQ pendant 3 tours. CD : 3 tours |
| 2 | Fausse Note | A | 1 | 1 | 30% de chance d'étourdir une cible 1 tour. CD : 3 tours |
| 3 | Complainte | P | 1 | 2 | Les ennemis debuffés par le Barde subissent -10% DEF en plus |
| 4 | Cacophonie | A | 2 | 2 | Tous les ennemis -10% toutes stats pendant 2 tours. CD : 6 tours |
| 5 | Solo Dévastateur | A | 2 | 3 | Dégâts soniques à 150% CHA + Étourdi 1 tour. CD : 5 tours |
| 6 | Chanson Maudite | P | 2 | 3 | Les ennemis debuffés ont 15% de chance de s'entre-attaquer |
| 7 | **Maître de la Discorde** | P | 3 | 3 | Les debuffs se propagent : quand un ennemi debuffé meurt, son debuff passe à l'ennemi le plus proche |

### Branche C — Faux Artiste (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Public Captif | P | 2 | 1 | Quand le trait se déclenche, les ennemis -10% VIT pendant 1 tour |
| 2 | Bis Repetita | R | 1 | 1 | Quand le Barde rate un buff (trait), il le relance au tour suivant sans cooldown |
| 3 | Impro Désastreuse | A | 2 | 2 | Buff ou debuff aléatoire sur cible aléatoire. 60% de chance favorable. CD : 3 tours |
| 4 | Auto-dérision | P | 1 | 2 | Chaque déclenchement du trait donne +10% CHA pour le combat |
| 5 | Berceuse | A | 2 | 3 | Endort un ennemi 2 tours. Si Narcoleptique, endort TOUS les ennemis 1 tour. CD : 7 tours |
| 6 | Standing Ovation | R | 2 | 3 | Si le trait se déclenche 3 fois dans un combat, toute l'équipe soignée à 50% PV max |
| 7 | **Génie Incompris** | R | 3 | 3 | Le trait donne un buff aléatoire à toute l'équipe à chaque déclenchement. +5% par stack dans le même combat |

---

## 7. BARBARE

*"RAAAAH. C'est tout ce que j'avais à dire."*

### Branche A — Rage (DPS pur)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Fureur | P | 1 | 1 | ATQ +15% |
| 2 | Frappe Sauvage | A | 2 | 1 | Attaque à 200% ATQ, le Barbare prend 10% PV max en dégâts. CD : 3 tours |
| 3 | Soif de Sang | P | 2 | 2 | Chaque attaque soigne 5% des dégâts infligés |
| 4 | Rage Croissante | P | 1 | 2 | ATQ +3% par tour de combat (cumulable) |
| 5 | Déchaînement | A | 2 | 3 | 3 attaques à 80% ATQ sur cibles aléatoires. CD : 5 tours |
| 6 | Dernier Souffle | R | 2 | 3 | Sous 20% PV, ATQ +50% |
| 7 | **Fureur Immortelle** | R | 3 | 3 | Quand le Barbare devrait mourir, reste à 1 PV + 100% ATQ pendant 2 tours. 1 fois par combat |

### Branche B — Brute (Tank offensif)
*Coût total : 14 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Masse Imposante | P | 1 | 1 | PV +15% |
| 2 | Coup de Tête | A | 2 | 1 | 120% ATQ + Étourdi cible 1 tour. Le Barbare aussi étourdi 1 tour. CD : 4 tours |
| 3 | Peau de Pierre | P | 2 | 2 | DEF +10% |
| 4 | Cri de Guerre | A | 1 | 2 | Équipe +10% ATQ 3 tours. Le Barbare +20%. CD : 5 tours |
| 5 | Balayage | A | 2 | 3 | Attaque tous les ennemis à 90% ATQ. CD : 4 tours |
| 6 | Endurci | P | 3 | 3 | Dégâts reçus -10% tant qu'au-dessus de 50% PV |
| 7 | **Titan** | P | 3 | 3 | PV +25%. Provoque auto l'ennemi le plus fort. Quand touché, riposte à 40% ATQ |

### Branche C — Destruction (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Casse Involontaire | R | 2 | 1 | Quand le trait se déclenche, tous les ennemis prennent 30% ATQ en dégâts |
| 2 | Adrénaline | P | 1 | 1 | Quand le trait se déclenche, VIT doublée pour 1 tour |
| 3 | Fureur Incontrôlable | P | 2 | 2 | Le Barbare attaque un ennemi aléatoire à 50% ATQ en fin de tour même si étourdi/endormi |
| 4 | Dommages Structurels | R | 1 | 2 | 20% de chance par attaque de réduire la DEF de la cible de 10% (permanent pour le combat) |
| 5 | Tremblement de Terre | A | 2 | 3 | 120% ATQ sur tous les ennemis. Tous les alliés prennent 5% PV max. CD : 7 tours |
| 6 | Instinct Bestial | P | 2 | 3 | Trait +5% de déclenchement mais chaque déclenchement donne +8% ATQ permanent |
| 7 | **Force de la Nature** | R | 3 | 3 | Chaque déclenchement du trait → onde de choc : 60% ATQ tous les ennemis + 10% chance d'étourdir. Si Pyromane, inflige aussi "En feu" |

---

## 8. NÉCROMANCIEN

*"Les morts sont plus fiables que les vivants. Ils se plaignent moins."*

### Branche A — Maître des Morts (Invocations)
*Coût total : 14 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Squelette Serviteur | A | 2 | 1 | Invoque un squelette (PV 30% du Nécro, ATQ 30% INT). Max 1. CD : 4 tours |
| 2 | Os Renforcés | P | 1 | 1 | Invocations +20% PV |
| 3 | Armée Grandissante | P | 1 | 2 | Max invocations +1 (max 2) |
| 4 | Golem d'Os | A | 2 | 2 | Invoque un golem (PV 60% du Nécro, ATQ 40% INT, provoque). Remplace les squelettes. CD : 8 tours |
| 5 | Lien Vital | P | 2 | 3 | Quand une invocation meurt, le Nécro récupère 15% PV max |
| 6 | Sacrifice | A | 3 | 3 | Détruit une invocation pour infliger 200% de ses PV en dégâts. CD : 5 tours |
| 7 | **Seigneur Liche** | P | 3 | 3 | Max invocations +1 (max 3). Invocations +10% ATQ par tour en vie. Quand un ennemi meurt, le Nécro l'invoque automatiquement |

### Branche B — Flétrisseur (DPS nécrotique)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Drain de Vie | A | 2 | 1 | 100% INT en dégâts et soigne du même montant. CD : 3 tours |
| 2 | Malédiction | A | 1 | 1 | Soins reçus par la cible -50% pendant 3 tours. CD : 4 tours |
| 3 | Aura de Mort | P | 2 | 2 | Tous les ennemis perdent 2% PV max par tour |
| 4 | Flétrissure | A | 1 | 2 | 140% INT + cible -15% DEF pendant 3 tours. CD : 4 tours |
| 5 | Pacte de Sang | A | 2 | 3 | Sacrifie 20% PV pour +40% INT pendant 3 tours. CD : 5 tours |
| 6 | Peste | P | 2 | 3 | Les ennemis empoisonnés transmettent le poison aux adjacents |
| 7 | **Faucheur** | P | 3 | 3 | Chaque kill donne +10% INT pour le combat. À 3 kills, les sorts drainent (50% dégâts → soins) |

### Branche C — Nécromancie Ratée (Branche du Défaut)
*Coût total : 13 points*

| # | Nom | Type | Coût | Palier | Effet |
|---|-----|------|------|--------|-------|
| 1 | Squelette Rebelle | R | 1 | 1 | Quand le trait se déclenche et qu'une invocation est active, elle attaque un ennemi aléatoire à 150% ATQ |
| 2 | Énergie Résiduelle | P | 2 | 1 | Quand le trait se déclenche, bouclier de 15% PV max |
| 3 | Invocation Instable | P | 2 | 2 | 10% de chance par tour qu'une invocation explose : mort + 150% PV en dégâts aux ennemis |
| 4 | Nécro-Accident | R | 1 | 2 | Quand un allié meurt, 40% de chance de le relever comme mort-vivant (20% PV, +30% ATQ, 3 tours) |
| 5 | Mort Temporaire | A | 2 | 3 | Le Nécro se tue 2 tours. Invocations +50% toutes stats. Revient avec 50% PV. CD : 10 tours |
| 6 | Chaos Nécrotique | P | 2 | 3 | Trait +5% de déclenchement. Chaque déclenchement crée un squelette gratuit |
| 7 | **Maître de l'Erreur** | R | 3 | 3 | Chaque déclenchement du trait invoque un mort-vivant aléatoire. Si 3 déclenchements, "Armée de l'Incompétence" : 5 morts-vivants faibles mais acharnés |

---

## Annexe A — Vérification des paliers

Chaque branche doit permettre d'atteindre 3 points au palier 1 et 6 points au palier 2 :

| Classe | Branche | Coûts P1 | Total P1 | Coûts P2 | Total P1+P2 | Coûts P3 | Total |
|--------|---------|----------|----------|----------|-------------|----------|-------|
| Guerrier | Rempart | 1+2 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Guerrier | Bras Armé | 1+2 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Guerrier | Calamité | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Mage | Élémentaliste | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Mage | Arcaniste | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Mage | Instabilité | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+1+3 | 12 |
| Voleur | Assassin | 1+2 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Voleur | Ombre | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Voleur | Filou | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Ranger | Tireur | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+3+3 | 14 |
| Ranger | Survivaliste | 1+2 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Ranger | Distrait | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Prêtre | Guérisseur | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+3+3 | 14 |
| Prêtre | Inquisiteur | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Prêtre | Foi Vacillante | 2+1 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Barde | Virtuose | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Barde | Provocateur | 2+1 | 3 ✓ | 1+2 | 6 ✓ | 2+2+3 | 13 |
| Barde | Faux Artiste | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Barbare | Rage | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Barbare | Brute | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+3+3 | 14 |
| Barbare | Destruction | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Nécro | Maître Morts | 2+1 | 3 ✓ | 1+2 | 6 ✓ | 2+3+3 | 14 |
| Nécro | Flétrisseur | 2+1 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |
| Nécro | Nécro Ratée | 1+2 | 3 ✓ | 2+1 | 6 ✓ | 2+2+3 | 13 |

✓ Toutes les 24 branches passent la vérification.

---

## Annexe B — Synergies trait négatif × branche du défaut

| Classe | Trait | Synergie notable |
|--------|-------|------------------|
| Guerrier + Catastrophe Ambulante | Couard | Le Guerrier fuit → un ennemi fuit aussi |
| Guerrier + Catastrophe Ambulante | Narcoleptique | S'endort → endort un ennemi |
| Mage + Bombe à Retardement | Pyromane | Meurt en mettant le feu → explosion massive |
| Voleur + Roi des Embrouilles | Kleptomane | Vol de loot + effet positif aléatoire |
| Ranger + Sniper Somnambule | Narcoleptique | Tire avant de s'endormir → DPS gratuit |
| Barde + Génie Incompris | Narcoleptique | Berceuse endort TOUS les ennemis |
| Barbare + Force de la Nature | Pyromane | Onde de choc + En feu = carnage AoE |
| Nécro + Maître de l'Erreur | Philosophe | Skip un tour mais invoque un mort-vivant gratuit |
| Prêtre + Hérétique Sacré | Philosophe | Skip un tour mais buff toute l'équipe |
