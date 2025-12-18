<?php
// footer.php - 3ED.I SOCIETY admin footer (include at end of pages)
?>
    </main> <!-- /admin-main -->
  </div> <!-- /admin-wrapper -->

  <footer class="admin-footer text-center py-3">
    <div class="container">
      <small>© <?php echo date('Y'); ?> 3ED.I SOCIETY. Admin panel. Designed by Iyang 3ED .I</small>
    </div>
  </footer>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Optional admin JS: place custom scripts here -->
  <script>
    // highlight active sidebar link
    (function() {
      const links = document.querySelectorAll('.admin-sidebar .nav-link');
      links.forEach(l => {
        if (l.href === location.href || location.href.includes(l.getAttribute('href'))) {
          l.classList.add('active');
        }
      });
    })();

    // Mobile sidebar toggle
    (function() {
      const toggler = document.querySelector('.navbar-toggler');
      const sidebar = document.querySelector('.admin-sidebar');
      const mainContent = document.querySelector('.admin-main');
      
      if (toggler && sidebar) {
        // Toggle sidebar when hamburger is clicked
        toggler.addEventListener('click', function(e) {
          e.stopPropagation();
          sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking on main content
        if (mainContent) {
          mainContent.addEventListener('click', function() {
            if (sidebar.classList.contains('show')) {
              sidebar.classList.remove('show');
            }
          });
        }
        
        // Close sidebar when clicking on a sidebar link
        const sidebarLinks = sidebar.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
          link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
              sidebar.classList.remove('show');
            }
          });
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
          if (!sidebar.contains(e.target) && !toggler.contains(e.target)) {
            sidebar.classList.remove('show');
          }
        });
      }
    })();
  </script>
</body>
</html>
