(function () {
    // Biz Location Finder v2.1.6.5
    // console.log('Running Biz Location Finder v2.1.6.5');
  
  // -----------------------
  // Data
  // -----------------------
  let businesses = []; // will be loaded from JSON
  let dynamicTabs = []; // will be built from actual data categories

  // -----------------------
  // Helpers
  // -----------------------
  
  // Frontend Heroicons SVG helper function
  // Heroicons SVG implementation
  function heroicon(name, className = '', size = '20') {
    const icons = {
      'map-pin': '<path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>',
      'phone': '<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd"/>',
      'envelope': '<path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>',
      'globe-alt': '<path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>',
      'camera': '<path d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path fill-rule="evenodd" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5a.75.75 0 0 0 0 1.5h.008a.75.75 0 0 0 0-1.5h-.008Z" clip-rule="evenodd"/>',

      'facebook':'<g transform="translate(1.5,1.5) scale(1.23)"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></g>',

      'instagram':'<g transform="translate(2.5,4) scale(1.23)"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/></svg></g>',

      
      'arrow-top-right-on-square': '<path fill-rule="evenodd" d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75H15.75a.75.75 0 0 1 0-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd"/>',

      'users': '<path fill-rule="evenodd" d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" clip-rule="evenodd"/>'
    };
    
    if (!icons[name]) {
      console.warn(`Icon "${name}" not found`);
      return '';
    }
    
    const classAttr = className ? ` class="blf-icon ${className}"` : ' class="blf-icon"';
    
    return `<svg${classAttr} width="${size}" height="${size}" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      ${icons[name]}
    </svg>`;
  }
  
  function buildDynamicTabsForContainer(container, businesses) {
    // Check if this is data view mode
    const isDataView = container?.getAttribute('data-view') === 'data';
    
    // For data view, return a single "All" tab to show all businesses
    if (isDataView) {
      return [{ key: 'All', id: 'all' }];
    }
    
    // Check if categories are limited via shortcode for this specific container
    const allowedCategories = container?.getAttribute('data-categories');
    let categoriesToShow = null;
    
    if (allowedCategories) {
      categoriesToShow = allowedCategories.split(',').map(c => c.trim().toLowerCase());
    }
    
    // Get unique categories from the data
    let uniqueCategories = [...new Set(businesses.map(b => b.category))].filter(Boolean);
    
    // Filter categories if shortcode specifies them
    if (categoriesToShow && !categoriesToShow.includes('all')) {
      // Simple case-insensitive matching - use exact category names from Google Sheets
      uniqueCategories = uniqueCategories.filter(category => 
        categoriesToShow.includes(category.toLowerCase())
      );
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
      let panel = container.querySelector(`#${container.id}-${tab.id}`);
      if (!panel) {
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
    // Handle escaped line endings first (literal \r\n in the text)
    let normalizedCSV = csvText.replace(/\\r\\n/g, '\n').replace(/\\r/g, '\n');
    
    // Then handle actual line endings
    normalizedCSV = normalizedCSV.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    
    // Fix escaped quotes that got mangled during line ending replacement
    normalizedCSV = normalizedCSV.replace(/\\"/g, '"');

    // Fix JSON-escaped slashes
    normalizedCSV = normalizedCSV.replace(/\\\//g, '/');

    
    // Split into lines
    const lines = normalizedCSV.trim().split('\n');
    
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
    const rawHeaders = parseCSVLine(lines[0]);
    const headers = rawHeaders.map(h => h.trim().toLowerCase());
    
    // Validate that headers were parsed correctly
    if (headers.length === 1 && headers[0].includes(',')) {
      // Fallback to simple split for headers (assuming no quotes in header row)
      const manualHeaders = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, '').toLowerCase());
      if (manualHeaders.length > 1) {
        headers.splice(0, headers.length, ...manualHeaders);
      } else {
        console.error('Both header parsing methods failed. CSV may be malformed.');
        return [];
      }
    }
    
    // Additional validation - ensure we have reasonable number of headers
    if (headers.length < 2) {
      console.error('CSV headers appear invalid - too few columns detected:', headers);
      return [];
    }
    
    // Parse data lines
     const data = lines.slice(1).map((line, lineIndex) => {
    // Skip empty lines
    if (line.trim() === '') return null;
      
    try {
      const values = parseCSVLine(line);
      
      // Apply manual split fallback if parsing failed (Safari compatibility)
      if (values.length === 1 && values[0].includes(',')) {
        const manualValues = line.split(',').map(v => v.trim().replace(/^"|"$/g, ''));
        if (manualValues.length > 1) {
          values.splice(0, values.length, ...manualValues);
        }
      }
      
      // Ensure values array has same length as headers (pad with empty strings)
      if (values.length < headers.length) {
        while (values.length < headers.length) values.push('');
      } else if (values.length > headers.length) {
        // If there are extra values, join the extras into the last field (defensive)
        const extras = values.slice(headers.length - 1).join(',');
        values.splice(headers.length - 1, values.length - (headers.length - 1), extras);
      }
    
      const obj = {};
      headers.forEach((header, index) => {
        const rawValue = values[index] || '';
        let decodedValue = decodeHTMLEntities(rawValue);
      
        if (header === 'category') {
          decodedValue = decodedValue.replace(/^["'`]+|["'`]+$/g, '').trim();
        }
      
        obj[header] = decodedValue;
      });
    
      // Validate object has required data
      if (!obj.name || obj.name.trim() === '') {
        return null;
      }
      
      return obj;
    } catch (error) {
      console.error(`Error parsing CSV line ${lineIndex + 1}:`, error);
      console.error('Problematic line:', line);
      return null; // Skip this line and continue
    }
  }).filter(Boolean);
    
    return data;
  }

  // Helper function to parse a single CSV line with proper quote handling
  function parseCSVLine(line) {
    // More robust CSV parsing that works consistently across browsers
    const values = [];
    let currentValue = '';
    let insideQuotes = false;
    let i = 0;
    
    while (i < line.length) {
      const char = line[i];
      
      if (char === '"') {
        if (insideQuotes) {
          // Check if this is an escaped quote (double quote)
          if (i + 1 < line.length && line[i + 1] === '"') {
            currentValue += '"';
            i += 2; // Skip both quotes
          } else {
            // End of quoted field
            insideQuotes = false;
            i++;
          }
        } else {
          // Start of quoted field
          insideQuotes = true;
          i++;
        }
      } else if (char === ',' && !insideQuotes) {
        // Field separator outside quotes
        values.push(currentValue.trim());
        currentValue = '';
        i++;
      } else {
        // Regular character
        currentValue += char;
        i++;
      }
    }
    
    // Add the last value
    values.push(currentValue.trim());
    
    return values;
  }
  // Render cards for a specific container and its dynamic tabs
  function renderCards(container, dynamicTabs) {
    dynamicTabs.forEach(tab => {
      // Find the tab content panel with the specific container ID
      const panel = container.querySelector(`#${container.id}-${tab.id}`);
      if (!panel) {
        console.error(`Panel not found for tab ${tab.id}`);
        return;
      }

      const grid = panel.querySelector('.grid');
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

      items.forEach(b => {
        const card = document.createElement('article');
        card.className = 'card';
        card.dataset.suburb = (b.suburb || '').toLowerCase();
        card.dataset.name = (b.name || '').toLowerCase(); // Add business name for searching

        const mapsHref = googleMapsLink(b.name, b.suburb);
        const igHandle = (b.instagram || '').toLowerCase();
        const igHref = igHandle ? `https://instagram.com/${encodeURIComponent(igHandle)}` : '#';

        const fbHandle = (b.facebook || '').toLowerCase();
        const fbHref = fbHandle ? `https://facebook.com/${encodeURIComponent(fbHandle)}` : '#';

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
        addressLink.title = b.address ? `View ${b.name} on Google Maps` : 'View on Google Maps';
        addressLink.innerHTML = `${heroicon('map-pin', 'contact-icon', '16')} ${b.address || ''}`;
            
        // Create Instagram link
        const igLink = document.createElement('a');
        igLink.href = igHref;
        igLink.target = '_blank';
        igLink.rel = 'noopener';
        igLink.title = igHandle ? `View ${b.name} on Instagram (@${igHandle})` : 'Instagram';
        igLink.innerHTML = `${heroicon('instagram', 'contact-icon', '20')} ${igHandle}`;
            
        // Create Facebook link
        const fbLink = document.createElement('a');
        fbLink.href = fbHref;
        fbLink.target = '_blank';
        fbLink.rel = 'noopener';
        fbLink.title = fbHandle ? `View ${b.name} on Facebook (@${fbHandle})` : 'Facebook';
        fbLink.innerHTML = `${heroicon('users', 'contact-icon', '20')} ${fbHandle}`;
        // Create website link if available

        const websiteLink = document.createElement('a');
        if (b.website && b.website.trim()) {
          websiteLink.href = b.website.startsWith('http') ? b.website : `https://${b.website}`;
          websiteLink.target = '_blank';
          websiteLink.rel = 'noopener';
          websiteLink.title = `Visit ${b.name} website`;
          websiteLink.innerHTML = `${heroicon('arrow-top-right-on-square', 'contact-icon', '16')} Visit Website`;
          websiteLink.className = 'biz-website';
        }

        // Create phone link if available
        const phoneLink = document.createElement('a');
        if (b.phone && b.phone.trim()) {
          phoneLink.href = `tel:${b.phone.replace(/\s/g, '')}`;
          phoneLink.title = `Call ${b.name}`;
          phoneLink.innerHTML = `${heroicon('phone', 'contact-icon', '16')} ${b.phone}`;
          phoneLink.className = 'biz-phone';
        }

        // Create email link if available
        const emailLink = document.createElement('a');
        if (b.email && b.email.trim()) {
          emailLink.href = `mailto:${b.email}`;
          emailLink.title = `Email ${b.name}`;
          emailLink.innerHTML = `${heroicon('envelope', 'contact-icon', '16')} ${b.email}`;
          emailLink.className = 'biz-email';
        }

        // Create description element if available
        const descriptionElement = document.createElement('p');
        if (b.description && b.description.trim()) {
          descriptionElement.className = 'biz-description';
          descriptionElement.textContent = b.description;
        }

        // Build the card structure
        card.innerHTML = `
          <div class="biz-name-container"></div>
          <p class="biz-meta"><span class="biz-suburb-container"></span></p>
          <p class="biz-address"></p>
          <div class="biz-contact-info">
            <p class="biz-instagram"></p>
            <p class="biz-facebook"></p>
            <p class="biz-website-container"></p>
            <p class="biz-phone-container"></p>
            <p class="biz-email-container"></p>
          </div>
          <div class="biz-description-container"></div>
        `;

        // Append the safe elements
        card.querySelector('.biz-name-container').appendChild(nameElement);
        card.querySelector('.biz-suburb-container').appendChild(suburbElement);
        card.querySelector('.biz-address').appendChild(addressLink);
        
        // Add Instagram link if data exists
        if (b.instagram && b.instagram.trim()) {
          card.querySelector('.biz-instagram').appendChild(igLink);
        } else {
          card.querySelector('.biz-instagram').style.display = 'none';
        }
        // Add Facebook link if data exists
        if (b.facebook && b.facebook.trim()) {
          card.querySelector('.biz-facebook').appendChild(fbLink);
        } else {
          card.querySelector('.biz-facebook').style.display = 'none';
        }

        // Add new fields if they exist
        if (b.website && b.website.trim()) {
          card.querySelector('.biz-website-container').appendChild(websiteLink);
        } else {
          card.querySelector('.biz-website-container').style.display = 'none';
        }
        
        if (b.phone && b.phone.trim()) {
          card.querySelector('.biz-phone-container').appendChild(phoneLink);
        } else {
          card.querySelector('.biz-phone-container').style.display = 'none';
        }
        
        if (b.email && b.email.trim()) {
          card.querySelector('.biz-email-container').appendChild(emailLink);
        } else {
          card.querySelector('.biz-email-container').style.display = 'none';
        }
        
        if (b.description && b.description.trim()) {
          card.querySelector('.biz-description-container').appendChild(descriptionElement);
        } else {
          card.querySelector('.biz-description-container').style.display = 'none';
        }

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
          // console.log(`Updated counter for ${tab.key}: ${items.length}`);
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

        // Remove active class from all nav items and panels in this container
        container.querySelectorAll('.tab-nav li').forEach(n => n.classList.remove('active'));
        container.querySelectorAll('.tab-content').forEach(p => p.classList.remove('active'));

        // Add active class to clicked nav item
        li.classList.add('active');

        // Find and activate the corresponding panel using ID instead of data-tab
        const panelId = `${containerId}-${target}`;
        const panel = document.getElementById(panelId);
        
        if (panel) {
          panel.classList.add('active');
          
          // Clear search on tab switch and show all cards
          const input = panel.querySelector('.search-input');
          if (input) {
            input.value = '';
            filterPanel(panel, '');
          }
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
    });

    // Only call revealCards once after filtering is complete
    if (visible > 0) {
      revealCards(panel);
    }

    const noResults = panel.querySelector('.no-results');
    if (noResults) noResults.hidden = visible !== 0;
  }

  function init() {
    // Process all x-stockists containers on the page
    const containers = document.querySelectorAll('.x-stockists');
    
    if (containers.length === 0) {
      console.error('No .x-stockists containers found on page');
      return;
    }
    
    // Process each container individually
    containers.forEach((container, index) => {
      const containerId = container.id || `blf-auto-${index}`;
      
      try {
        // Build dynamic tabs for this specific container
        const containerTabs = buildDynamicTabsForContainer(container, businesses);
        
        // Initialize this specific container
        initializeContainer(containerId, containerTabs);
      } catch (error) {
        console.error(`❌ Error processing container ${containerId}:`, error);
        console.error('Error stack:', error.stack);
      }
    });
  }
  // Initialize a specific container by ID with its dynamic tabs
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

  // Injects the animated 3-dot loading indicator into each container.
  // Called immediately when the data fetch begins so the user sees feedback right away.
  function showLoadingIndicators() {
    document.querySelectorAll('.x-stockists').forEach(c => {
      const el = document.createElement('div');
      el.className = 'blf-loading';
      el.innerHTML = '<h2>Loading</h2><span></span><span></span><span></span>';
      
      c.appendChild(el);
    });
  }

  // Fades out and removes the loading indicator once data is ready (or on error).
  // Adds the 'blf-loading-hide' class to trigger the CSS opacity transition,
  // then removes the element from the DOM only after the transition completes.
  function removeLoadingIndicators() {
    document.querySelectorAll('.blf-loading').forEach(el => {
      el.classList.add('blf-loading-hide');
      // Remove from DOM after fade-out transition ends — avoids a jarring instant removal
      el.addEventListener('transitionend', () => el.remove(), { once: true });
    });
  }

  function fetchBusinessesAndInit() {
    showLoadingIndicators();
    // Use the new unified data endpoint that respects admin settings
    const dataUrl = myPluginData.apiUrl;
    
    fetch(dataUrl)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        // Check if response is JSON (database) or text (CSV)
        const contentType = res.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return res.json().then(data => ({ type: 'json', data }));
        } else {
          return res.text().then(data => ({ type: 'csv', data }));
        }
      })
      .then(response => {
        if (response.type === 'json') {
          // Database data - extract from response structure
          if (response.data && response.data.success && response.data.data) {
            businesses = response.data.data;
            // console.log(`Successfully loaded ${businesses.length} businesses from database`);
          } else if (response.data && Array.isArray(response.data)) {
            // Direct array format
            businesses = response.data;
            // console.log(`Successfully loaded ${businesses.length} businesses from database`);
          } else {
            // Empty or invalid database response
            businesses = [];
            // console.log('Database returned empty or invalid data');
          }
        } else {
          // CSV data - needs parsing
          businesses = parseCSV(response.data);
        }
        
        if (businesses.length === 0) {
          removeLoadingIndicators();
          // Handle empty data gracefully - show "no businesses" message instead of error
          const container = document.querySelector('.x-stockists');
          if (container) {
            container.innerHTML = `
              <div class="blf-no-businesses">
                <h3>No businesses found</h3>
                <p>There are currently no businesses saved in your directory.</p>
                <p><small>Add businesses through the WordPress admin or configure your Google Sheets data source.</small></p>
              </div>
            `;
          }
          return; // Exit early, don't call init()
        }
        
        removeLoadingIndicators();
        init();
      })
      .catch(err => {
        removeLoadingIndicators();
        console.error('Failed to load business data:', err);
        
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
