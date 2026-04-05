# 👹 Bestiaire — Monstres & Boss

## 1. Constantes paramétrables (table `game_settings`)

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `MONSTER_ELITE_CHANCE` | 8 | % de chance qu'un monstre soit une variante élite |
| `MONSTER_ELITE_STAT_MULT` | 150 | % des stats normales pour un élite |
| `MONSTER_ELITE_LOOT_BONUS` | 50 | % de bonus de loot pour un élite |
| `MONSTER_ELITE_XP_BONUS` | 30 | % de bonus d'XP pour un élite |
| `MONSTER_SKILL_COOLDOWN_MIN` | 2 | CD minimum des compétences de monstres |
| `MONSTER_SKILL_COOLDOWN_MAX` | 5 | CD maximum |
| `MINIBOSS_STAT_MULT` | 200 | % des stats d'un monstre normal de la zone |
| `MINIBOSS_LOOT_RARITY_MIN` | 2 | Rareté minimum du loot de mini-boss (2 = Peu commun) |
| `BOSS_STAT_MULT` | 300 | % des stats d'un monstre normal de la zone |
| `BOSS_LOOT_RARITY_MIN` | 3 | Rareté minimum du loot de boss (3 = Rare) |
| `BOSS_PHASE_HP_THRESHOLD` | 50 | % PV sous lequel le boss passe en phase 2 |
| `WORLD_BOSS_HP_PER_PLAYER` | 5000 | PV ajoutés au boss mondial par joueur actif |
| `WORLD_BOSS_MECHANIC_INTERVAL` | 3 | Tours entre chaque activation de mécanique spéciale |

---

## 2. Système élémentaire

### 2.1 — Les 6 éléments

| Élément | Couleur | Zones associées |
|---------|---------|-----------------|
| Physique | Gris | Prairie, Mines |
| Feu | Rouge | Volcan, zones arides |
| Glace | Bleu | Zones hivernales, souterrains profonds |
| Foudre | Jaune | Tour du Mage, tempêtes |
| Poison | Vert | Marais, Forêt |
| Sacré/Ombre | Violet | Cimetière, zones maudites |

### 2.2 — Table des faiblesses et résistances

| Attaquant ↓ / Défenseur → | Physique | Feu | Glace | Foudre | Poison | Sacré/Ombre |
|---------------------------|----------|-----|-------|--------|--------|-------------|
| Physique | 100% | 100% | 100% | 100% | 100% | 100% |
| Feu | 100% | 50% | 150% | 100% | 120% | 80% |
| Glace | 100% | 150% | 50% | 80% | 100% | 120% |
| Foudre | 100% | 100% | 120% | 50% | 80% | 150% |
| Poison | 100% | 80% | 100% | 120% | 50% | 150% |
| Sacré/Ombre | 100% | 120% | 80% | 150% | 150% | 50% |

> Les valeurs sont des **multiplicateurs de dégâts en %**. 150% = super efficace, 50% = résistant, 100% = neutre. Tout en entiers.

### 2.3 — Affinité élémentaire des monstres

Chaque monstre a **une affinité élémentaire** qui détermine :
- Le type de ses attaques
- Ses résistances passives
- Sa faiblesse exploitable

```
Application des dégâts :
  Dégâts_finaux = Dégâts_nets × Multiplicateur_élément / 100

Exemple : Attaque de Feu contre monstre de Glace
  Dégâts_nets = 65
  Multiplicateur = 150%
  Dégâts_finaux = 65 × 150 / 100 = 97
```

### 2.4 — Sources élémentaires des héros

Par défaut, les héros infligent des dégâts **Physiques**. Les sources élémentaires viennent de :
- Compétences de classe (Mage = Feu/Glace/Foudre selon les talents)
- Effets d'objets (une épée avec "dégâts de feu")
- Buffs de quêtes ou de consommables
- Certains traits en synergie (Pyromane → dégâts de Feu)

---

## 3. Compétences de monstres

Chaque monstre possède **1 à 2 compétences** en plus de son attaque de base.

### 3.1 — Pool de compétences

**Offensives :**

| ID | Nom | Effet | CD |
|----|-----|-------|-----|
| MO01 | Morsure Vicieuse | 130% ATQ + 20% chance d'Empoisonné (3 tours) | 3 |
| MO02 | Charge | 150% ATQ sur une cible, le monstre prend 10% de ses PV max | 4 |
| MO03 | Souffle Élémentaire | 80% ATQ à tous les héros (élément du monstre) | 5 |
| MO04 | Frappe Ciblée | 180% ATQ sur le héros avec le moins de PV | 3 |
| MO05 | Drain | 100% ATQ + soigne le monstre de 50% des dégâts | 4 |
| MO06 | Attaque Sournoise | 120% ATQ, ignore 30% DEF | 3 |
| MO07 | Explosion Suicide | Le monstre meurt + inflige 200% ATQ à tous les héros | 1× (mort) |
| MO08 | Griffure Profonde | 110% ATQ + Saignement (3% PV max/tour, 3 tours) | 3 |
| MO09 | Cri Strident | 60% ATQ à tous les héros + 25% chance Étourdi 1 tour | 4 |
| MO10 | Embuscade | 200% ATQ, utilisable uniquement au 1er tour | 1× |

**Défensives / Support :**

| ID | Nom | Effet | CD |
|----|-----|-------|-----|
| MD01 | Carapace | +50% DEF pendant 2 tours | 4 |
| MD02 | Régénération | Récupère 8% PV max | 3 |
| MD03 | Hurlement | Tous les monstres alliés gagnent +15% ATQ 2 tours | 5 |
| MD04 | Bouclier Élémentaire | Immunité à son élément pendant 2 tours | 5 |
| MD05 | Fuite Tactique | Le monstre se retire 1 tour (invulnérable) puis revient avec +20% ATQ | 5 |
| MD06 | Invocation | Invoque 1 monstre faible de la zone (50% stats du monstre) | 6 |
| MD07 | Transfert de Vie | Sacrifie 20% PV pour soigner un allié monstre de 30% PV max | 3 |
| MD08 | Provocation | Force un héros aléatoire à cibler ce monstre pendant 2 tours | 4 |
| MD09 | Miroir | Renvoie 30% des dégâts reçus pendant 1 tour | 4 |
| MD10 | Camouflage | Esquive +30% pendant 2 tours | 5 |

---

## 4. Variantes élites

### 4.1 — Déclenchement

```
Quand un monstre est généré pour un combat :
  Jet : random(1, 100) <= MONSTER_ELITE_CHANCE (8%)
  Si oui → le monstre devient une variante élite
```

### 4.2 — Préfixes élites

Chaque élite reçoit un **préfixe** aléatoire qui modifie ses stats et lui donne un bonus unique :

| Préfixe | Modificateur stats | Bonus spécial |
|---------|-------------------|---------------|
| Enragé | ATQ ×180%, DEF ×90% | Attaque deux fois quand sous 30% PV |
| Blindé | DEF ×200%, VIT ×80% | Réduit les critiques reçus de 50% |
| Rapide | VIT ×200%, PV ×90% | Agit toujours en premier, 15% esquive bonus |
| Vampirique | Stats ×130% | Toutes ses attaques soignent de 20% des dégâts |
| Toxique | Stats ×120% | Toutes ses attaques empoisonnent (2% PV/tour, 3 tours) |
| Géant | PV ×250%, VIT ×70% | Ses attaques touchent 2 héros |
| Spectral | Stats ×110% | 25% de chance de résister à toute attaque (miss total) |
| Béni | Stats ×120% | Régénère 3% PV max par tour |
| Explosif | ATQ ×150%, PV ×80% | À la mort, explose : 100% ATQ à tous les héros |
| Ancien | Stats ×160% | Possède 1 compétence supplémentaire |

### 4.3 — Récompenses élites

```
XP = XP_normale × (100 + MONSTER_ELITE_XP_BONUS) / 100
Loot chance = Loot_chance_normale × (100 + MONSTER_ELITE_LOOT_BONUS) / 100
Rareté minimum du loot = Peu commun (garanti)
```

### 4.4 — Affichage

- Le nom du monstre est préfixé : "Rat Enragé", "Gobelin Spectral"
- Une aura colorée dans l'UI (couleur du préfixe)
- Le Narrateur prévient : "Oh, celui-là a l'air différent. Pas dans le bon sens."

---

## 5. Bestiaire par zone

### Zone 1 — La Prairie des Débutants (Niv. 1-5)
*"Là où même les monstres ont l'air de s'ennuyer."*

**Élément dominant : Physique**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Rat Peureux | Physique | 15 | 5 | 2 | 12 | MO10 (Embuscade) | Fuit si seul survivant |
| Slime Vert | Poison | 25 | 3 | 5 | 4 | MD02 (Régénération) | Attaque le plus proche |
| Gobelin Chapardeur | Physique | 20 | 8 | 3 | 10 | MO06 (Sournoise) | Cible le héros avec le plus d'or |
| Loup Solitaire | Physique | 30 | 10 | 4 | 14 | MO08 (Griffure) | Cible le héros le plus faible en PV |
| Épouvantail Animé | Physique | 35 | 6 | 8 | 3 | MD08 (Provocation), MO09 (Cri) | Provoque puis crie |
| Abeille Géante | Poison | 12 | 7 | 1 | 16 | MO07 (Explosion Suicide) | Attaque puis explose |

**Mini-boss : Le Fermier Possédé** (Niv. 5)
- Élément : Sacré/Ombre
- PV : 150, ATQ : 20, DEF : 12, VIT : 8
- Compétences : MO04 (Frappe Ciblée), MD06 (Invocation — invoque des Rats)
- Mécanique : Invoque 2 Rats au tour 1. Tant qu'un Rat est vivant, il a +20% DEF
- Loot : Fourche du Fermier (Arme Peu commun, ATQ + bonus dégâts aux morts-vivants)

**Boss : Le Taureau du Pré Maudit** (Niv. 5)
- Élément : Physique
- PV : 400, ATQ : 25, DEF : 15, VIT : 10
- Phase 1 (>50% PV) : MO02 (Charge), MD01 (Carapace)
  - Alterne charge et défense. Prévisible.
- Phase 2 (≤50% PV) : Gagne MO03 (Souffle — souffle physique), ATQ +30%
  - "Le taureau est furieux. Vous n'auriez peut-être pas dû lui tirer la queue."
- Loot garanti : Corne du Taureau (Accessoire Rare, ATQ +15, Charge : 10% de chance d'étourdir en attaquant)

---

### Zone 2 — La Forêt des Elfes Vexés (Niv. 5-12)
*"Les arbres sont beaux. Les elfes sont insupportables."*

**Élément dominant : Poison / Physique**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Araignée Tisseuse | Poison | 35 | 12 | 6 | 10 | MO01 (Morsure Vicieuse) | Cible le héros sans résistance poison |
| Elfe Renégat | Physique | 40 | 15 | 8 | 14 | MO06 (Sournoise), MD10 (Camouflage) | Camouflage au tour 1, attaque sournoise |
| Treant Grognon | Physique | 80 | 10 | 18 | 2 | MD01 (Carapace), MD03 (Hurlement) | Buff ses alliés, tanke |
| Loup-Garou Végétarien | Physique | 50 | 18 | 7 | 16 | MO08 (Griffure), MO02 (Charge) | Charge au tour 1, puis griffures |
| Fée Maléfique | Poison | 20 | 8 | 3 | 20 | MO09 (Cri Strident), MD05 (Fuite) | Hit & run, très agile |
| Champignon Ambulant | Poison | 45 | 6 | 12 | 3 | MD02 (Régénération), MO07 (Explosion) | Tanke puis explose quand bas en PV |
| Sanglier Cuirassé | Physique | 60 | 14 | 14 | 8 | MO02 (Charge) | Fonce sur le héros le plus lourd en DEF |

**Mini-boss : L'Archère Elfe Vexée** (Niv. 10)
- Élément : Physique
- PV : 200, ATQ : 30, DEF : 10, VIT : 22
- Compétences : MO04 (Frappe Ciblée), MO10 (Embuscade), MD10 (Camouflage)
- Mécanique : Tire toujours sur le héros avec le plus de PV max ("Commence par le gros."). Se camoufle tous les 3 tours.
- Loot : Arc de la Vexée (Arme Rare, ATQ + 10% critique contre cibles à PV max)

**Boss : Le Grand Chêne Furieux** (Niv. 12)
- Élément : Poison
- PV : 700, ATQ : 22, DEF : 25, VIT : 4
- Phase 1 (>50% PV) : MD01 (Carapace), MD06 (Invocation — 2 Treants), MO03 (Souffle Poison)
  - Tanke derrière sa carapace et invoque des sbires
- Phase 2 (≤50% PV) : Perd Carapace, gagne MO09 (Cri — les racines tremblent), ATQ +40%, VIT +10
  - "L'arbre s'est arraché du sol. Il court. UN ARBRE COURT."
- Loot garanti : Écorce Vivante (Armure Rare, DEF + Régénération 1% PV/tour + faiblesse Feu reçue +20%)

---

### Zone 3 — Les Mines du Nain Ivre (Niv. 12-20)
*"L'alcool coule à flots. Le sang aussi."*

**Élément dominant : Physique / Feu**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Golem de Pierre | Physique | 100 | 15 | 25 | 2 | MD01 (Carapace), MD09 (Miroir) | Ultra tank, lent |
| Chauve-Souris de Mine | Physique | 25 | 12 | 3 | 22 | MO09 (Cri Strident) | Cible aléatoire, agaçant |
| Nain Mineur Fantôme | Sacré/Ombre | 55 | 20 | 10 | 12 | MO05 (Drain), MD07 (Transfert) | Drain + soigne ses alliés |
| Salamandre de Lave | Feu | 60 | 22 | 8 | 14 | MO03 (Souffle Feu), MO01 (Morsure) | AoE feu puis morsures |
| Ver Minier | Physique | 80 | 18 | 5 | 6 | MO02 (Charge), MO07 (Explosion) | Charge puis explose à 20% PV |
| Mimic (Coffre Piégé) | Physique | 70 | 25 | 15 | 1 | MO10 (Embuscade), MD05 (Fuite) | Embuscade massive tour 1, puis fuit si touché |

**Mini-boss : Le Contremaître Fantôme** (Niv. 17)
- Élément : Sacré/Ombre
- PV : 350, ATQ : 28, DEF : 18, VIT : 10
- Compétences : MO05 (Drain), MD03 (Hurlement), MD06 (Invocation — Nains Fantômes)
- Mécanique : Buff ses invocations puis drain. Quand ses invocations meurent, il gagne +10% ATQ par invocation morte.
- Loot : Pioche Spectrale (Arme Rare, ATQ + INT, dégâts Sacré/Ombre, +20% dégâts aux morts-vivants)

**Boss : Le Roi sous la Montagne (version ivre)** (Niv. 20)
- Élément : Feu / Physique
- PV : 1200, ATQ : 35, DEF : 22, VIT : 8
- Phase 1 (>50% PV) : MO02 (Charge), MD01 (Carapace), MO03 (Souffle Feu)
  - Bourré mais costaud. Trébuche parfois (15% de chance de rater son tour — le Narrateur adore)
- Phase 2 (≤50% PV) : Jette sa chope → MO03 devient AoE Feu à 120% ATQ. Perd la chance de rater. Gagne MO04 (Frappe Ciblée)
  - "Il a posé sa bière. C'est sérieux maintenant."
- Loot garanti : Chope du Roi (Accessoire Épique, PV +20%, ATQ +10%, 10% de chance par tour de "Boire un coup" : soin 8% PV max mais -5% VIT 1 tour)

---

### Zone 4 — Le Marais de la Bureaucratie (Niv. 20-30)
*"Les monstres ici ont des formulaires. Et ils INSISTENT pour que vous les remplissiez."*

**Élément dominant : Poison / Glace**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Bureaucrate Zombie | Poison | 80 | 20 | 15 | 6 | MO09 (Cri — "SUIVANT !"), MD08 (Provocation) | Force les héros à "faire la queue" (provocation) |
| Grenouille Géante Toxique | Poison | 65 | 25 | 8 | 14 | MO01 (Morsure Poison), MO02 (Charge) | Saute sur la cible la plus lointaine |
| Archiviste Spectral | Glace | 50 | 15 | 10 | 18 | MO06 (Sournoise — paperasse coupante), MD04 (Bouclier) | Esquive et contre-attaque |
| Tamponneur | Physique | 90 | 28 | 20 | 4 | MO04 (Frappe Ciblée — TAMPON!), MD01 (Carapace) | Estampille lentement mais fortement |
| Sangsue Administrative | Poison | 40 | 12 | 5 | 16 | MO05 (Drain), MO05 (Drain) | Double drain, vole la vie ET les stats |
| Stagiaire Perdu | Physique | 30 | 8 | 3 | 20 | MD05 (Fuite), MD06 (Invocation — appelle d'autres stagiaires) | Fuit et appelle des renforts. Inoffensif seul |
| Crocodile en Cravate | Poison | 100 | 30 | 12 | 10 | MO08 (Griffure), MO01 (Morsure) | Agressif pur, cible la plus faible DEF |

**Mini-boss : Le Chef de Service** (Niv. 26)
- Élément : Glace
- PV : 500, ATQ : 32, DEF : 22, VIT : 12
- Compétences : MD08 (Provocation — "Prenez un ticket"), MD06 (Invocation — Stagiaires ×3), MO09 (Cri — "C'EST L'HEURE DE LA RÉUNION")
  - Mécanique : Invoque des Stagiaires qui ne font rien d'utile mais absorbent les dégâts. Le Chef ne peut être ciblé que si tous les Stagiaires sont éliminés.
  - "Derrière chaque Chef de Service, il y a trois Stagiaires qui prennent les coups."
- Loot : Badge du Chef (Accessoire Rare, DEF + CHA, effet "Paperasserie" : 10% de chance d'infliger skip tour à un ennemi)

**Boss : Le Directeur Général du Marais** (Niv. 30)
- Élément : Poison / Glace
- PV : 1800, ATQ : 38, DEF : 28, VIT : 10
- Phase 1 (>50% PV) : MD06 (Invocation — 1 Chef de Service), MO03 (Souffle Poison — "Mémo toxique"), MD01 (Carapace — "Blindage contractuel")
  - "Il a un bureau. Dans un marais. Il est assis derrière. En plein combat."
- Phase 2 (≤50% PV) : Se lève de son bureau. Perd DEF -30% mais gagne ATQ +50%. MO04 (Frappe — "Licenciement"), nouveau sort : "Audit" — échange ATQ et DEF d'un héros pendant 3 tours
  - "Il a rangé ses dossiers. Il va vous ranger aussi."
- Loot garanti : Stylo-Plume de Pouvoir (Arme Épique, ATQ + INT, dégâts Poison, effet spécial "Signature Mortelle" : 15% de chance que l'attaque inflige un debuff aléatoire)

---

### Zone 5 — La Tour du Mage Distrait (Niv. 30-42)
*"Les lois de la physique sont des suggestions ici."*

**Élément dominant : Foudre / Glace**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Balai Enchanté | Foudre | 60 | 18 | 5 | 24 | MO06 (Sournoise), MD10 (Camouflage) | Ultra rapide, esquive |
| Livre Mordeur | Physique | 50 | 30 | 8 | 12 | MO01 (Morsure — pages coupantes), MO10 (Embuscade) | Ressemble à un livre normal puis mord |
| Élémentaire Instable | Foudre | 80 | 25 | 10 | 14 | MO03 (Souffle Foudre), MO07 (Explosion) | AoE puis explose à basse vie |
| Armure Animée | Physique | 120 | 20 | 30 | 4 | MD01 (Carapace), MD09 (Miroir) | Tank pur, reflète les dégâts |
| Apprenti Raté | Foudre | 45 | 22 | 6 | 16 | MO03 (Souffle — sort aléatoire), MD05 (Fuite) | Lance des sorts imprévisibles (élément aléatoire) |
| Gargouille | Glace | 90 | 24 | 20 | 8 | MO02 (Charge — piqué aérien), MD04 (Bouclier) | Charge depuis les airs, immunité élémentaire temporaire |
| Horloge Folle | Foudre | 70 | 15 | 12 | 30 | MO09 (Cri — BONG!), spécial: à chaque tour, accélère un ennemi ou ralentit un héros | Manipule l'initiative |

**Mini-boss : Le Familiar Rebelle** (Niv. 37)
- Élément : Foudre
- PV : 650, ATQ : 40, DEF : 15, VIT : 25
- Compétences : MO03 (Souffle Foudre), MO04 (Frappe Ciblée), MD05 (Fuite), spécial : "Inversion de Sort" — 20% de chance de retourner un buff allié en debuff
- Mécanique : Ciblé les buffs de l'équipe pour les inverser. Punition pour les équipes qui buffent beaucoup.
- Loot : Collier du Familiar (Accessoire Rare, INT + VIT, +10% résistance Foudre, les buffs durent 1 tour de plus)

**Boss : Le Mage Distrait lui-même** (Niv. 42)
- Élément : Foudre / Glace / Feu (change)
- PV : 2500, ATQ : 45, DEF : 18, VIT : 16
- Phase 1 (>50% PV) : Change d'élément aléatoirement tous les 3 tours (Feu → Glace → Foudre). Ses attaques et résistances changent avec l'élément. MO03 (Souffle élémentaire), MD04 (Bouclier élémentaire)
  - "Il ne sait plus quel sort il lançait. Honnêtement, lui non plus."
- Phase 2 (≤50% PV) : Panique. Lance 2 sorts par tour au lieu de 1, mais 30% de chance que le sort se retourne contre lui-même (auto-dégâts à 50%). Gagne MO09 (Cri — "POURQUOI EST-CE QUE TOUT EXPLOSE?!")
  - "Le Mage a perdu le contrôle. C'est à la fois terrifiant et hilarant."
- Loot garanti : Bâton du Distrait (Arme Épique, INT élevée, dégâts élémentaires aléatoires, effet "Distraction" : 15% de chance que le sort touche une cible aléatoire au lieu de la cible choisie — peut toucher un ennemi différent OU un allié)

---

### Zone 6 — Le Cimetière Syndiqué (Niv. 42-55)
*"Les morts-vivants ont des droits. Et des heures de pause."*

**Élément dominant : Sacré/Ombre**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Squelette Syndiqué | Sacré/Ombre | 80 | 25 | 15 | 10 | MD03 (Hurlement — "GRÈVE!"), spécial : ne combat pas les tours 4 et 8 (pause syndicale) | Pause obligatoire |
| Zombie Fonctionnaire | Sacré/Ombre | 110 | 20 | 20 | 4 | MO05 (Drain — "Taxe vitale"), MD02 (Régénération) | Lent, drain + regen |
| Fantôme Plaintif | Sacré/Ombre | 60 | 30 | 5 | 20 | MO06 (Sournoise — traverse les armures), MD10 (Camouflage) | Ignore la DEF partiellement |
| Goule Affamée | Sacré/Ombre | 90 | 35 | 8 | 16 | MO01 (Morsure), MO08 (Griffure) | DPS pur, cible les blessés |
| Vampire Comptable | Sacré/Ombre | 75 | 28 | 12 | 18 | MO05 (Drain ×2), MD07 (Transfert) | Vole la vie de tout le monde |
| Revenant en Armure | Physique | 140 | 22 | 28 | 6 | MD01 (Carapace), MD09 (Miroir), MO02 (Charge) | Tank mort-vivant, résistant |

**Mini-boss : Le Délégué Syndical des Morts** (Niv. 50)
- Élément : Sacré/Ombre
- PV : 900, ATQ : 38, DEF : 25, VIT : 12
- Compétences : MD03 (Hurlement — "TOUS EN GRÈVE!"), MD06 (Invocation — Squelettes Syndiqués ×3), MO05 (Drain)
- Mécanique : "Grève Générale" — tous les 5 tours, TOUS les morts-vivants (y compris les invocations) arrêtent de combattre pendant 1 tour. MAIS au tour suivant, ils ont tous +30% ATQ (retour de grève furieux).
- Loot : Pancarte de Grève (Arme Rare, ATQ + CHA, "Slogan" : 20% de chance que l'attaque fasse skip le tour de l'ennemi)

**Boss : Le Nécromancien Retraité** (Niv. 55)
- Élément : Sacré/Ombre
- PV : 3200, ATQ : 42, DEF : 20, VIT : 14
- Phase 1 (>50% PV) : MD06 (Invocation — 2 Revenants en Armure), MO05 (Drain), MD04 (Bouclier Ombre)
  - Reste derrière ses invocations. Les Revenants le protègent (il ne peut être ciblé que si les 2 Revenants sont morts, mais il les réinvoque tous les 6 tours).
- Phase 2 (≤50% PV) : Perd la capacité d'invocation. Gagne "Forme Spectrale" : 25% esquive, ignore 20% DEF sur toutes ses attaques. MO04 (Frappe Ciblée — "Doigt de Mort"), MO03 (Souffle Ombre — AoE)
  - "Il a décidé de faire le travail lui-même. La retraite est finie."
- Loot garanti : Grimoire du Retraité (Arme Épique, INT très élevée, dégâts Sacré/Ombre, effet "Dernière Volonté" : quand le porteur meurt, invoque un Revenant avec 50% des stats du porteur pendant 5 tours)

---

### Zone 7 — Le Volcan du Dragon Retraité (Niv. 55-70)
*"Le dragon ne veut pas se battre. Ses locataires, par contre..."*

**Élément dominant : Feu**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Élémentaire de Feu | Feu | 100 | 35 | 10 | 16 | MO03 (Souffle Feu), MO07 (Explosion) | AoE feu agressif, explose en mourant |
| Diablotin Farceur | Feu | 55 | 28 | 5 | 28 | MO06 (Sournoise), MD05 (Fuite), MO10 (Embuscade) | Hit & run permanent |
| Tortue de Lave | Feu | 180 | 18 | 35 | 2 | MD01 (Carapace), MD09 (Miroir), MD02 (Régénération) | Tank ultime, inarrêtable en défense |
| Phénix Mineur | Feu | 80 | 32 | 8 | 20 | MO03 (Souffle), spécial : à sa mort, renaît 1 fois avec 50% PV | Renaît une fois |
| Forgeron Damné | Feu | 120 | 38 | 18 | 10 | MO04 (Frappe — marteau), MD03 (Hurlement — buff feu) | Buff ses alliés puis frappe fort |
| Wyrm de Lave | Feu | 150 | 30 | 15 | 12 | MO02 (Charge), MO08 (Griffure), MO01 (Morsure) | Multi-attaques physiques + feu |

**Mini-boss : Le Gardien du Volcan** (Niv. 65)
- Élément : Feu
- PV : 1300, ATQ : 50, DEF : 30, VIT : 8
- Compétences : MO03 (Souffle Feu — massif), MD01 (Carapace), MO02 (Charge), spécial : "Éruption" (tous les 4 tours, dégâts de feu à 60% ATQ sur tout le monde, y compris lui-même)
- Mécanique : L'Éruption le blesse aussi — stratégie : survivre aux éruptions et le laisser s'auto-détruire.
- Loot : Bouclier de Magma (Armure Rare, DEF élevée, résistance Feu +30%, effet : quand touché par du Feu, gagne +10% ATQ 2 tours)

**Boss : Le Dragon Retraité** (Niv. 70)
- Élément : Feu
- PV : 5000, ATQ : 55, DEF : 35, VIT : 12
- **Mécanique unique : Le Dragon ne veut PAS se battre.** Il faut le provoquer.
  - Les 3 premiers tours, le Dragon ne fait RIEN sauf parler.
    - "Je suis à la retraite. Allez-vous-en."
    - "Non, sérieusement. J'ai du thé à finir."
    - "Bon, si vous insistez..."
  - Après 3 tours, le combat commence vraiment.
- Phase 1 (>50% PV) : MO03 (Souffle Feu — dévastateur), MD04 (Bouclier Feu), MO02 (Charge)
  - Puissant mais alterne attaque et pause thé (skip 1 tour sur 4)
- Phase 2 (≤50% PV) : Plus de pauses. Souffle Feu à 150% ATQ. Gagne "Fureur Draconique" : +20% toutes stats. MO04 (Frappe Ciblée — queue)
  - "Vous avez renversé son thé. Vous allez payer."
- Loot garanti : Écaille du Retraité (Armure Légendaire, DEF très élevée, résistance Feu +40%, effet "Sagesse du Dragon" : +10% XP toute l'équipe, "Pause Thé" : 1×/combat, skip un tour pour récupérer 25% PV max)

---

### Zone 8 — La Capitale des Incompétents (Niv. 70-85)
*"La ville où les héros sont des zéros et les zéros sont... toujours des zéros."*

**Élément dominant : Mixte (tous les éléments)**

| Monstre | Élément | PV | ATQ | DEF | VIT | Compétences | Comportement |
|---------|---------|-----|-----|-----|-----|-------------|-------------|
| Garde Corrompu | Physique | 140 | 40 | 25 | 12 | MO04 (Frappe), MD08 (Provocation) | Tanky, force le combat |
| Voleur de Grand Chemin | Physique | 90 | 45 | 10 | 24 | MO10 (Embuscade), MO06 (Sournoise), MD10 (Camouflage) | Burst puis disparaît |
| Mage de Rue | Foudre | 75 | 38 | 8 | 18 | MO03 (Souffle Foudre), MD05 (Fuite) | Cast & run |
| Golem de la Guilde | Physique | 200 | 30 | 35 | 4 | MD01 (Carapace), MD09 (Miroir), MO02 (Charge) | Supertank construit par la guilde locale |
| Rat d'Égout Mutant | Poison | 100 | 35 | 12 | 20 | MO01 (Morsure Poison), MD06 (Invocation — Rats ×4) | Envahit avec des petits rats |
| Marchand Rival | Physique | 110 | 25 | 15 | 14 | MD03 (Hurlement — "SOLDES!"), MO05 (Drain — "vol de clientèle") | Débuff économique narratif |
| Assassin de la Guilde | Sacré/Ombre | 80 | 50 | 5 | 26 | MO10 (Embuscade), MO06 (Sournoise), MO04 (Frappe Ciblée) | Glass cannon, cible le healer |
| Statue Vivante | Physique | 250 | 20 | 40 | 2 | MD09 (Miroir), spécial : immobile, ne bouge que si attaqué | Ne fait rien tant qu'on ne la touche pas |

**Mini-boss : Le Maître de la Guilde des Zéros** (Niv. 78)
- Élément : Physique
- PV : 1600, ATQ : 48, DEF : 28, VIT : 16
- Compétences : MO04 (Frappe), MD06 (Invocation — 1 Garde + 1 Assassin), MD03 (Hurlement — "À l'attaque! ...enfin, si vous voulez")
- Mécanique : Ses invocations sont incompétentes — 20% de chance par tour qu'elles s'entre-attaquent au lieu d'attaquer les héros. Le Maître est compétent, lui.
- Loot : Médaille du Mérite (Accessoire Rare, CHA + toutes stats +5%, effet "Leadership Douteux" : 10% de chance que les monstres ennemis ratent leur tour dans la zone)

**Boss : Le Maire de la Capitale** (Niv. 85)
- Élément : Mixte (change selon la phase)
- PV : 6000, ATQ : 52, DEF : 30, VIT : 14
- **Mécanique unique : 3 phases, chacune avec un élément différent**
- Phase 1 — Politique (>66% PV, Glace) : Discours gelant. MD08 (Provocation — "CITOYENS!"), MO03 (Souffle Glace — "Budget gelé"), MD06 (Invocation — 2 Gardes Corrompus)
  - "Il fait un discours. C'est paralysant. Littéralement."
- Phase 2 — Corruption (66-33% PV, Poison) : Pot-de-vin. MO05 (Drain — "Taxes"), MO01 (Morsure Poison — "Clause cachée"), spécial : chaque tour, un héros aléatoire perd 50 or
  - "Il vous fait PAYER pour le combattre. C'est du génie."
- Phase 3 — Rage (≤33% PV, Feu) : Pète les plombs. ATQ +50%. MO03 (Souffle Feu — "BUDGET INCENDIAIRE!"), MO04 (Frappe — "Coup de Tampon Final"), MO02 (Charge)
  - "Le Maire a retourné son bureau. La politique, c'est fini."
- Loot garanti : Écharpe du Maire (Accessoire Légendaire, toutes stats +8%, "Immunité Diplomatique" : 1×/combat ignore complètement une attaque, "Corruption" : +15% or trouvé)

---

## 6. Boss mondiaux (rappel + détails)

### 6.1 — Stats des boss mondiaux

```
PV = WORLD_BOSS_HP_PER_PLAYER × nombre_joueurs_actifs
ATQ = Niveau_moyen_serveur × 6
DEF = Niveau_moyen_serveur × 4
VIT = Niveau_moyen_serveur × 2
```

### 6.2 — Les 5 boss mondiaux pré-définis

**Boss 1 : Le Dragon Retraité (version énervée)** (Niv. recommandé 15+)
- Élément : Feu
- Mécaniques :
  1. "Pause Café" : Tous les WORLD_BOSS_MECHANIC_INTERVAL (3) tours, le dragon s'arrête pour boire son thé → fenêtre de vulnérabilité (+30% dégâts reçus pendant 1 tour)
  2. "Souffle Dévastateur" : Après sa pause, souffle de feu massif à 150% ATQ à toute l'équipe
- Stratégie : Timer les gros dégâts pendant les pauses café

**Boss 2 : Le Kraken Comptable** (Niv. recommandé 25+)
- Élément : Glace / Poison
- Mécaniques :
  1. "Audit" : Tous les 3 tours, échange ATQ et DEF d'un héros aléatoire pendant 3 tours
  2. "Tentacules" : Attaque 3 héros simultanément à 70% ATQ chacun
  3. "Encre Noire" : 20% de chance par tour d'aveugler un héros (-30% précision, 2 tours)
- Stratégie : Équipes équilibrées ATQ/DEF pour minimiser l'impact de l'Audit

**Boss 3 : L'Ancien Bureaucrate** (Niv. recommandé 40+)
- Élément : Poison / Glace
- Mécaniques :
  1. "Immunité Administrative" : Immune à tous les dégâts SAUF si l'attaquant a le debuff "Formulaire Rempli" (obtenu en subissant l'attaque "Paperasserie" du boss et en survivant 2 tours)
  2. "Paperasserie" : Cible un héros, lui inflige skip 1 tour + "Formulaire en cours" (debuff). Au bout de 2 tours, le debuff se transforme en "Formulaire Rempli" (buff qui permet de frapper le boss)
  3. "Tampon de Rejet" : Retire les "Formulaire Rempli" d'un héros tous les 5 tours
- Stratégie : Rotation — laisser un héros se faire "papier" pour qu'il puisse frapper ensuite

**Boss 4 : Le Géant Narcoleptique** (Niv. recommandé 55+)
- Élément : Physique
- Mécaniques :
  1. "Narcolepsie" : Le géant s'endort aléatoirement (25% par tour). Quand il dort : vulnérabilité totale (+50% dégâts reçus) pendant 1-2 tours
  2. "Réveil Brutal" : Quand il se réveille, attaque TOUS les héros à 200% ATQ (AoE massive)
  3. "Ronflements" : Pendant le sommeil, les ronflements infligent 5% PV max par tour à toute l'équipe (dégâts soniques)
- Stratégie : DPS maximum pendant le sommeil, tank/soin maximum au réveil

**Boss 5 : La Hydre Syndicaliste** (Niv. recommandé 70+)
- Élément : Poison / Feu
- Mécaniques :
  1. "Têtes Multiples" : Commence avec 3 têtes. Chaque tête attaque séparément à 60% ATQ
  2. "Grève" : Quand une tête est coupée (PV d'une tête = PV_total / 5), elle "fait grève" → disparaît 3 tours
  3. "Repousse Syndicale" : Après la grève, la tête repousse avec +20% stats ET une deuxième tête pousse à 50% des stats de la première
  4. "Négociation" : Si toutes les têtes sont en grève simultanément, le corps est vulnérable 2 tours
- Stratégie : Couper toutes les têtes en même temps pour avoir une fenêtre sur le corps. Si on les coupe une par une, le boss devient de plus en plus fort

### 6.3 — Boss mondiaux générés par IA (phase 2+)

```json
{
  "prompt": "Génère un boss de serveur pour un idle RPG humoristique. Style Kaamelott/Naheulbeuk. Le boss doit avoir un nom absurde, une description drôle, un élément parmi [Physique, Feu, Glace, Foudre, Poison, Sacré/Ombre], et 2-3 mécaniques de combat uniques et intéressantes stratégiquement. Les mécaniques doivent alterner entre des phases de vulnérabilité et des phases dangereuses. Format JSON.",
  "output": {
    "name": "string",
    "description": "string (2-3 phrases humoristiques)",
    "element": "string",
    "mechanics": [
      {
        "name": "string",
        "description": "string",
        "trigger": "every_X_turns | hp_threshold | random_chance",
        "trigger_value": "integer",
        "effect": "string (description mécanique précise)"
      }
    ]
  }
}
```

---

## 7. Tableau récapitulatif du bestiaire

| Zone | Monstres | Mini-boss | Boss | Éléments | Niveaux |
|------|----------|-----------|------|----------|---------|
| Prairie | 6 | Le Fermier Possédé | Le Taureau du Pré Maudit | Physique, Poison | 1-5 |
| Forêt | 7 | L'Archère Elfe Vexée | Le Grand Chêne Furieux | Poison, Physique | 5-12 |
| Mines | 6 | Le Contremaître Fantôme | Le Roi sous la Montagne | Physique, Feu, Ombre | 12-20 |
| Marais | 7 | Le Chef de Service | Le Directeur Général | Poison, Glace | 20-30 |
| Tour | 7 | Le Familiar Rebelle | Le Mage Distrait | Foudre, Glace | 30-42 |
| Cimetière | 6 | Le Délégué Syndical | Le Nécromancien Retraité | Sacré/Ombre | 42-55 |
| Volcan | 6 | Le Gardien du Volcan | Le Dragon Retraité | Feu | 55-70 |
| Capitale | 8 | Le Maître de la Guilde | Le Maire de la Capitale | Mixte | 70-85 |
| **Total** | **53** | **8** | **8** | — | **1-85** |
| Boss mondiaux | — | — | **5** | Mixte | **15-70+** |
