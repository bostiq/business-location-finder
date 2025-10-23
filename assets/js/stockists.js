(function () {
    // Biz Location Finder v2.0.3 - Data view mode feature added
    console.log('Running Biz Location Finder v2.0.3');
  
  // -----------------------
  // Data
  // -----------------------
  let businesses = []; // will be loaded from JSON
  let dynamicTabs = []; // will be built from actual data categories

  // -----------------------
  // Helpers
  // -----------------------
  
  function buildDynamicTabsForContainer(container, businesses) {
    console.log('=== DEBUGGING CATEGORY FILTERING ===');
    console.log('Container:', container);
    console.log('All businesses:', businesses);
    console.log('Business categories:', businesses.map(b => b.category));
    
    // Check if this is data view mode
    const isDataView = container?.getAttribute('data-view') === 'data';
    console.log('Is data view mode:', isDataView);
    
    // For data view, return a single "All" tab to show all businesses
    if (isDataView) {
      console.log('Data view mode - returning single tab for all businesses');
      return [{ key: 'All', id: 'all' }];
    }
    
    // Check if categories are limited via shortcode for this specific container
    const allowedCategories = container?.getAttribute('data-categories');
    let categoriesToShow = null;
    
    console.log('Container data-categories attribute:', allowedCategories);
    
    if (allowedCategories) {
      categoriesToShow = allowedCategories.split(',').map(c => c.trim().toLowerCase());
      console.log('Categories limited by shortcode:', categoriesToShow);
    } else {
      console.log('No data-categories attribute found - building tabs from CSV data');
    }
    
    // Get unique categories from the data
    let uniqueCategories = [...new Set(businesses.map(b => b.category))].filter(Boolean);
    
    // Filter categories if shortcode specifies them
    if (categoriesToShow && !categoriesToShow.includes('all')) {
      // Simple case-insensitive matching - use exact category names from Google Sheets
      uniqueCategories = uniqueCategories.filter(category => 
        categoriesToShow.includes(category.toLowerCase())
      );
      console.log('Shortcode categories requested:', categoriesToShow);
      console.log('Available categories from data:', [...new Set(businesses.map(b => b.category))]);
      console.log('Filtered categories (case-insensitive):', uniqueCategories);
    }
    
    // Create slug from category name
    function createSlug(category) {
      return category.toLowerCase()
        .replace(/\s+/g, '-')      // Replace spaces with hyphens
        .replace(/[^\w-]/g, '')    // Remove non-word chars except hyphens
        .replace(/--+/g, '-')      // Replace multiple hyphens with single
        .replace(/^-|-$/g, '');    // Remove leading/trailing hyphens
    }
    
    // Always start with "All" tab (unless specifically excluded)
    const tabs = [];
    if (!categoriesToShow || categoriesToShow.includes('all')) {
      tabs.push({ key: 'All', id: 'all' });
    }
    
    // Add tabs for each unique category
    uniqueCategories.forEach(category => {
      tabs.push({
        key: category,
        id: createSlug(category)
      });
    });
    
    console.log('Dynamic tabs built:', tabs);
    return tabs;
  }
  
  // Create missing tab panels in the DOM for a specific container
  function createMissingTabPanels(tabs, container) {
    const tabPanelsContainer = container.querySelector('.tab-panels');
    if (!tabPanelsContainer) return;
    
    // Check if we should show search (look for container setting)
    const shouldShowSearch = !container?.hasAttribute('data-search-disabled');
    
    // Check if this is data view mode
    const isDataView = container?.getAttribute('data-view') === 'data';
    
    tabs.forEach((tab, index) => {
      // Check if panel already exists in this container
      let panel = container.querySelector(`#${tab.id}`);
      if (!panel) {
        console.log(`Creating missing panel for: ${tab.key}`);
        
        // Create new panel with unique ID scoped to this container
        panel = document.createElement('div');
        panel.className = 'tab-content';
        panel.id = `${container.id}-${tab.id}`;
        panel.setAttribute('data-tab', tab.id);
        panel.setAttribute('data-category', tab.key);
        
        // For data view or first tab, make it active
        if (isDataView || index === 0) {
          panel.classList.add('active');
        }
        
        // Add search and grid structure
        const searchHTML = shouldShowSearch ? `
          <div class="controls">
            <input class="search-input" type="text" placeholder="Search by business name or suburb…" />
          </div>
        ` : '';
        
        panel.innerHTML = `
          ${searchHTML}
          <p class="no-results" hidden="hidden">Sorry, no matches found.</p>
          <div class="grid"></div>
        `;
        
        tabPanelsContainer.appendChild(panel);
      }
    });
  }
  
  // Create missing tab navigation items for a specific container
  function createMissingTabNavigation(tabs, container) {
    const tabNav = container.querySelector('.tab-nav');
    if (!tabNav) return;
    
    // Check if we should show counters (look for container setting)
    const shouldShowCounters = !container?.hasAttribute('data-counters-disabled');
    
    tabs.forEach((tab, index) => {
      // Check if nav item already exists in this container
      let navItem = container.querySelector(`.tab-nav li[data-tab="${tab.id}"]`);
      if (!navItem) {
        console.log(`Creating missing nav item for: ${tab.key}`);
        
        // Create new nav item
        navItem = document.createElement('li');
        navItem.className = index === 0 ? 'tab-menu-item active' : 'tab-menu-item';
        navItem.setAttribute('data-tab', tab.id);
        navItem.setAttribute('data-container', container.id);
        navItem.textContent = tab.key;
        
        // Add counter badge if enabled
        if (shouldShowCounters) {
          const badge = document.createElement('div');
          badge.className = 'counter-badge';
          navItem.appendChild(badge);
        }
        
        tabNav.appendChild(navItem);
      }
    });
  }
  
  // Sanitize HTML to prevent XSS but avoid double encoding
  function sanitizeHTML(str) {
    if (!str) return '';
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
  }
  
  // Decode HTML entities from CSV data
  function decodeHTMLEntities(str) {
    if (!str) return '';
    const temp = document.createElement('div');
    temp.innerHTML = str;
    return temp.textContent || temp.innerText || '';
  }
  
  function googleMapsLink(name, suburb) {
    // Use the original text directly, then encode for URL
    const query = `${name}, ${suburb}, South Australia`;
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
  }

  // -----------------------
  // Rendering
  // -----------------------
  function revealCards(panel) {
  const cards = panel.querySelectorAll('.card');
  cards.forEach((card, i) => {
    card.classList.remove('visible'); // reset
    setTimeout(() => {
      card.classList.add('visible');
    }, i * 50); // stagger by 50ms
  });
}
  // CSV parsing helper function
  function parseCSV(csvText) {
    // Debug the raw input first
    console.log('Raw CSV length:', csvText.length);
    console.log('Raw CSV first 100 chars:', csvText.substring(0, 100));
    console.log('Contains \\r\\n:', csvText.includes('\r\n'));
    console.log('Contains \\n:', csvText.includes('\n'));
    console.log('Contains \\r:', csvText.includes('\r'));
    console.log('Contains literal \\\\r\\\\n:', csvText.includes('\\r\\n'));
    
    // Handle escaped line endings first (literal \r\n in the text)
    let normalizedCSV = csvText.replace(/\\r\\n/g, '\n').replace(/\\r/g, '\n');
    console.log('After escaped line ending replacement:', normalizedCSV.substring(0, 100));
    
    // Then handle actual line endings
    normalizedCSV = normalizedCSV.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    console.log('After actual line ending replacement:', normalizedCSV.substring(0, 100));
    
    // Fix escaped quotes that got mangled during line ending replacement
    normalizedCSV = normalizedCSV.replace(/\\"/g, '"');
    console.log('After quote fixing:', normalizedCSV.substring(0, 100));
    
    // Split into lines
    const lines = normalizedCSV.trim().split('\n');
    
    console.log(`CSV split into ${lines.length} lines`);
    console.log('First line (headers):', lines[0]);
    if (lines.length > 1) {
      console.log('Second line (first data):', lines[1]);
    }
    
    if (lines.length === 0) {
      console.error('CSV file is empty');
      return [];
    }
    
    // If we still only have 1 line after proper normalization, then there's an issue
    if (lines.length === 1) {
      console.error('CSV could not be split into multiple lines after normalization');
      console.error('Normalized CSV:', normalizedCSV.substring(0, 200));
      return [];
    }
    
    return parseCSVWithLines(lines);
  }
  
  // Helper function to parse CSV when we have proper lines
  function parseCSVWithLines(lines) {
    // Parse headers - the first line should be split properly
    console.log('Raw header line:', JSON.stringify(lines[0]));
    const headers = parseCSVLine(lines[0]);
    console.log('CSV Headers:', headers);
    
    // Validate that headers were parsed correctly
    if (headers.length === 1 && headers[0].includes(',')) {
      console.error('Header parsing failed - got one field with commas. Attempting manual split...');
      // Fallback to simple split for headers (assuming no quotes in header row)
      const manualHeaders = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
      console.log('Manual header parsing result:', manualHeaders);
      if (manualHeaders.length > 1) {
        headers.splice(0, headers.length, ...manualHeaders);
      }
    }
    
    const data = lines.slice(1).map((line, lineIndex) => {
      // Skip empty lines
      if (line.trim() === '') {
        return null;
      }

      console.log(`Parsing line ${lineIndex + 1}:`, JSON.stringify(line));
      const values = parseCSVLine(line);
      console.log(`Parsed values for line ${lineIndex + 1}:`, values);
      
      // Create object from headers and values
      const obj = {};
      headers.forEach((header, index) => {
        const rawValue = values[index] || '';
        let decodedValue = decodeHTMLEntities(rawValue);
        
        // Clean up category values - remove extra quotes that might be in the data
        if (header === 'category') {
          decodedValue = decodedValue.replace(/^["'`]+|["'`]+$/g, '').trim();
          console.log(`Category cleaned: "${rawValue}" -> "${decodedValue}"`);
        }
        
        console.log(`Field "${header}": "${rawValue}" -> "${decodedValue}"`);
        obj[header] = decodedValue;
      });
      
      // Debug first few rows
      if (lineIndex < 3) {
        console.log(`Row ${lineIndex + 1} final object:`, obj);
      }
      
      return obj;
    }).filter(Boolean); // Filter out any null (empty) lines
    
    console.log(`Parsed ${data.length} businesses from CSV`);
    return data;
  }

  // Helper function to parse a single CSV line with proper quote handling
  function parseCSVLine(line) {
    const values = [];
    let currentValue = '';
    let insideQuotes = false;
    
    for (let i = 0; i < line.length; i++) {
      const char = line[i];
      const nextChar = line[i + 1];
      
      // Handle escaped quotes (\")
      if (char === '\\' && nextChar === '"') {
        currentValue += '"'; // Add the actual quote character
        i++; // Skip the next character since we processed it
      } else if (char === '"') {
        insideQuotes = !insideQuotes;
        // Don't add the quote character to the value
      } else if (char === ',' && !insideQuotes) {
        values.push(currentValue.trim());
        currentValue = '';
      } else {
        currentValue += char;
      }
    }
    
    // Add the last value
    values.push(currentValue.trim());
    
    return values;
  }

  function renderCards(container, dynamicTabs) {
    console.log(`\n=== RENDERING CARDS for container: ${container.id} ===`);
    console.log('Dynamic tabs to render:', dynamicTabs);
    console.log('Available businesses:', businesses);
    
    dynamicTabs.forEach(tab => {
      console.log(`\nProcessing tab: ${tab.key} (${tab.id})`);
      
      // Find the tab content panel with the specific container ID
      const panel = container.querySelector(`#${container.id}-${tab.id}`);
      console.log('Found panel:', panel);
      if (!panel) {
        console.error(`Panel not found for tab ${tab.id}`);
        return;
      }

      const grid = panel.querySelector('.grid');
      console.log('Found grid:', grid);
      if (!grid) {
        console.error(`Grid not found in panel for tab ${tab.id}`);
        return;
      }

      grid.innerHTML = '';

      const items = tab.key === 'All'
        ? businesses.slice().sort((a, b) => a.name.localeCompare(b.name, 'en', { sensitivity: 'base' }))
        : businesses
            .filter(b => b.category.toLowerCase() === tab.key.toLowerCase())
            .sort((a, b) => a.name.localeCompare(b.name, 'en', { sensitivity: 'base' }));

      console.log(`Items for tab ${tab.key}:`, items);
      console.log(`Items count: ${items.length}`);

      items.forEach(b => {
        const card = document.createElement('article');
        card.className = 'card';
        card.dataset.suburb = (b.suburb || '').toLowerCase();
        card.dataset.name = (b.name || '').toLowerCase(); // Add business name for searching

        const mapsHref = googleMapsLink(b.name, b.suburb);
        const igHandle = (b.instagram || '').replace(/^@/, '');
        const igHref = igHandle ? `https://instagram.com/${encodeURIComponent(igHandle)}` : '#';

        // Create text nodes to avoid HTML entity double encoding
        const nameElement = document.createElement('h4');
        nameElement.className = 'biz-name';
        nameElement.textContent = b.name || '';

        const suburbElement = document.createElement('span');
        suburbElement.className = 'biz-suburb';
        suburbElement.textContent = b.suburb || '';

        const addressLink = document.createElement('a');
        addressLink.href = mapsHref;
        addressLink.target = '_blank';
        addressLink.rel = 'noopener';
        addressLink.textContent = b.address || '';

        const igLink = document.createElement('a');
        igLink.href = igHref;
        igLink.target = '_blank';
        igLink.rel = 'noopener';
        igLink.textContent = `@${igHandle}`;

        // Build the card structure
        card.innerHTML = `
          <div class="biz-name-container"></div>
          <p class="biz-meta"><span class="biz-suburb-container"></span></p>
          <p class="biz-address"></p>
          <p class="biz-instagram"></p>
        `;

        // Append the safe elements
        card.querySelector('.biz-name-container').appendChild(nameElement);
        card.querySelector('.biz-suburb-container').appendChild(suburbElement);
        card.querySelector('.biz-address').appendChild(addressLink);
        card.querySelector('.biz-instagram').appendChild(igLink);

        grid.appendChild(card);
      });

      // Reveal cards after adding them
      revealCards(panel);

      const noResults = panel.querySelector('.no-results');
      if (noResults) noResults.hidden = items.length !== 0;
      
      // Update counter badge
      const navItem = container.querySelector(`[data-tab="${tab.id}"][data-container="${container.id}"]`);
      if (navItem) {
        const badge = navItem.querySelector('.counter-badge');
        if (badge) {
          badge.textContent = items.length;
          console.log(`Updated counter for ${tab.key}: ${items.length}`);
        }
      }
    });
  }

  function setupTabs(container) {
    const navItems = container.querySelectorAll('.tab-nav li');
    const panels = container.querySelectorAll('.tab-content');

    navItems.forEach(li => {
      li.addEventListener('click', () => {
        const target = li.getAttribute('data-tab');
        const containerId = li.getAttribute('data-container');
        if (!target || !containerId) return;

        // Only affect tabs within this container
        navItems.forEach(n => n.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        li.classList.add('active');

        const panel = container.querySelector(`[data-tab="${target}"]`);
        if (!panel) return;

        panel.classList.add('active');

        // Clear search on tab switch and show all cards
        const input = panel.querySelector('.search-input');
        if (input) {
          input.value = '';
          filterPanel(panel, '');
        }
      });
    });
  }

  function setupSearch(container) {
    container.querySelectorAll('.tab-content').forEach(panel => {
      const input = panel.querySelector('.search-input');
      if (!input) return;

      input.addEventListener('input', e => {
        // Sanitize and validate search input
        let query = e.target.value || '';
        query = query.trim().toLowerCase();
        
        // Limit search query length for security
        if (query.length > 100) {
          query = query.substring(0, 100);
          e.target.value = query;
        }
        
        // Remove any potential script tags or HTML
        query = sanitizeHTML(query);
        
        filterPanel(panel, query);
      });
    });
  }

  function filterPanel(panel, query) {
    const cards = panel.querySelectorAll('.card');
    let visible = 0;

    cards.forEach(card => {
      const suburb = card.dataset.suburb || '';
      const businessName = card.dataset.name || '';
      
      // Search in both business name and suburb
      const show = query === '' || 
                   suburb.includes(query) || 
                   businessName.includes(query);
      
      card.style.display = show ? '' : 'none';
      if (show) visible++;
      revealCards(panel);
    });

    const noResults = panel.querySelector('.no-results');
    if (noResults) noResults.hidden = visible !== 0;
  }

  function init() {
    // Process all x-stockists containers on the page
    const containers = document.querySelectorAll('.x-stockists');
    console.log(`Found ${containers.length} x-stockists containers`);
    
    if (containers.length === 0) {
      console.error('No .x-stockists containers found on page');
      return;
    }
    
    containers.forEach((container, index) => {
      const containerId = container.id || `blf-auto-${index}`;
      console.log(`\n=== Processing container ${index + 1}: ${containerId} ===`);
      console.log('Container element:', container);
      console.log('Container data-categories:', container.getAttribute('data-categories'));
      
      try {
        // Build dynamic tabs for this specific container
        const containerTabs = buildDynamicTabsForContainer(container, businesses);
        console.log('✅ Built tabs for container:', containerTabs);
        
        // Initialize this specific container
        initializeContainer(containerId, containerTabs);
        console.log('✅ Initialized container:', containerId);
      } catch (error) {
        console.error(`❌ Error processing container ${containerId}:`, error);
        console.error('Error stack:', error.stack);
      }
    });
  }
  
  function initializeContainer(containerId, dynamicTabs) {
    const container = document.getElementById(containerId);
    if (!container) {
      console.error(`Container ${containerId} not found`);
      return;
    }
    
    // Ensure all necessary DOM elements exist for this container
    createMissingTabNavigation(dynamicTabs, container);
    createMissingTabPanels(dynamicTabs, container);
    
    // Set the first tab as active for this container
    const firstTab = container.querySelector('.tab-nav li');
    if (firstTab) firstTab.classList.add('active');
    
    const firstPanel = container.querySelector('.tab-content');
    if (firstPanel) firstPanel.classList.add('active');
    
    renderCards(container, dynamicTabs);
    setupTabs(container);
    setupSearch(container);

    // Ensure default tab has proper filter state
    const active = container.querySelector('.tab-content.active');
    if (active) filterPanel(active, '');
  }

  function fetchBusinessesAndInit() {
    // WordPress REST API endpoint for the plugin
    const csvUrl = '/wp-json/jq-stockists/v1/get-csv';

    fetch(csvUrl)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.text();
      })
      .then(csvText => {
        console.log('CSV loaded, first 200 chars:', csvText.substring(0, 200));
        businesses = parseCSV(csvText);
        if (businesses.length === 0) {
          throw new Error('No businesses parsed from CSV');
        }
        
        console.log(`Successfully loaded ${businesses.length} businesses`);
        console.log('Sample business categories:', businesses.slice(0, 5).map(b => b.category));
        
        init();
      })
      .catch(err => {
        console.error('Failed to load businesses.csv', err);
        
        // Show user-friendly error message
        const container = document.querySelector('.x-stockists');
        if (container) {
          container.innerHTML = `
            <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
              <h3>Unable to load business data</h3>
              <p>There was a problem loading the business directory. Please try refreshing the page.</p>
              <p><small>Error: ${err.message}</small></p>
            </div>
          `;
        }
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchBusinessesAndInit);
  } else {
    fetchBusinessesAndInit();
  }
})();
