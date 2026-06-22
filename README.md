# Dolibarr Factory Module (Gestion de la fabrication)

This is a customized version of the Factory module for Dolibarr, designed to handle Bills of Materials (BOM / Nomenclatures) and Manufacturing Orders (Ordres de Fabrication).

## Overview

The Factory module allows you to:
- Define complex Bill of Materials (Nomenclatures) for products.
- Manage sub-components, needed quantities, and warehouse locations.
- Track production flows and generate related documents (PDFs).

## Integration with Stock Reservation

This module works seamlessly with the **Calcul de Stock / Stock Movement Module** to provide a complete manufacturing and stock management workflow.

You can find and install the companion module here:
👉 **[DOLIBARR-STOCK-MOVEMENT-MODULE (Calcul de Stock)](https://github.com/Baati-Yossri/DOLIBARR-STOCK-MOVEMENT-MODULE)**

When both modules are used together, you can:
- Automatically calculate stock availability based on Factory BOMs.
- Reserve materials from specific warehouses before launching production.
- Finalize and permanently consume reserved stock upon production completion.
- Generate unified PDF reports that display both production requirements and real-time reservation statuses (`(RÉSERVÉ)`, `(CONSOMMÉ)`, etc.) for each component.

## Installation

1. Clone or download this repository into your Dolibarr `custom` directory (e.g., `htdocs/custom/factory`).
2. Go to **Setup > Modules** in your Dolibarr admin panel.
3. Find the **Factory** module and click the toggle to enable it.
4. Set up permissions and default settings.

## Credits

Original module architecture based on the Patas-Monkey Factory module, with heavy customizations for precise stock tracking, PDF alignment, and real-time reservation workflows.
