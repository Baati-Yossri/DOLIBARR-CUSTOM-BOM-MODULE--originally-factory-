-- --------------------------------------------------------

--
-- Structure de la table llx_product_factory
--
-- Contient la composition des produits en fabrication

CREATE TABLE IF NOT EXISTS llx_product_factory (
  rowid				integer NOT NULL AUTO_INCREMENT,
  fk_product_father	integer NOT NULL DEFAULT 0,		-- clé produit composé
  fk_product_children	integer NOT NULL DEFAULT 0,		-- clé produit composant
  pmp					double(24,8) DEFAULT 0,			-- prix unitaire d'achat
  price				double(24,8) DEFAULT 0,			-- prix unitaire de vente
  qty 				double DEFAULT NULL,			-- quantité entrant dans la fabrication
  ordercomponent		integer NOT NULL DEFAULT 0,			-- l'ordre d'affichage des composants
  globalqty			integer NOT NULL DEFAULT 0,			-- La quantité est à prendre au détail ou au global
  description			text,         					-- description
  PRIMARY KEY (rowid),
  UNIQUE KEY uk_product_factory (fk_product_father,fk_product_children),
  KEY idx_product_factory_fils (fk_product_children)
) ENGINE=InnoDB ;
