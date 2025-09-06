// Funções JavaScript principais
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema carregado!');
    
    // Auto-hide alerts após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });

    const closeButtons = document.querySelectorAll('.close-button');

    
    closeButtons.forEach(button => {
       
        button.addEventListener('click', () => {
          
            const alertDiv = button.parentElement;
                        
            alertDiv.style.display = 'none';
        });
    });
});