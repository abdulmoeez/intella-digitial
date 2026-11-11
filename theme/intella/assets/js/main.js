/**
 * Front-end entry point, loaded on every page as an ES module (deferred).
 * Only the features every page needs: the top bar, header navigation, mega menu, footer and forms.
 * Section features (tabs, carousels, sliders…) are loaded by the blocks that use them (block.json → viewScriptModule).
 * Each module starts itself on import.
 */

import '@intella/topbar';
import '@intella/navigation';
import '@intella/mega-menu';
import '@intella/footer-groups';
import '@intella/phone-fields';
import '@intella/form-validation';
import '@intella/campaign-fields';
import '@intella/year';
