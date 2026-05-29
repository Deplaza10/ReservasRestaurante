import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('restaurant-map-container')) {
        initRestaurantMap();
    }
});

function initRestaurantMap() {
    const containerId = 'restaurant-map-container';
    const container = document.getElementById(containerId);
    
    // Config values passed from Blade
    const config = window.APP_CONFIG;
    
    // Create Konva Stage
    const stage = new Konva.Stage({
        container: containerId,
        width: container.offsetWidth,
        height: 600,
    });

    const bgLayer = new Konva.Layer();
    const tablesLayer = new Konva.Layer();
    const tooltipLayer = new Konva.Layer();
    
    stage.add(bgLayer);
    stage.add(tablesLayer);
    stage.add(tooltipLayer);
    
    // Tooltip setup
    const tooltipGroup = new Konva.Group({ opacity: 0 });
    const tooltipBg = new Konva.Rect({
        fill: '#1a1a2e',
        cornerRadius: 4,
        stroke: '#e94560',
        strokeWidth: 1,
        shadowColor: '#000',
        shadowBlur: 10,
        shadowOpacity: 0.5
    });
    const tooltipText = new Konva.Text({
        text: '',
        fontFamily: 'Inter',
        fontSize: 13,
        padding: 10,
        fill: '#fff'
    });
    tooltipGroup.add(tooltipBg).add(tooltipText);
    tooltipLayer.add(tooltipGroup);

    let currentMesas = [];
    let selectedMesaId = null;
    
    // Fetch and render
    async function fetchDisponibilidad() {
        const fecha = document.getElementById('search-fecha').value;
        const horaInicio = document.getElementById('search-hora-inicio').value;
        const horaFin = document.getElementById('search-hora-fin').value;
        
        if (horaInicio >= horaFin) {
            alert("La hora de inicio debe ser anterior a la hora de fin");
            return;
        }

        try {
            document.getElementById('loading-map').style.display = 'block';
            tablesLayer.destroyChildren();
            
            const response = await axios.get(config.apiDisponibilidadUrl, {
                params: { fecha, hora_inicio: horaInicio, hora_fin: horaFin }
            });
            
            currentMesas = response.data;
            renderMesas(currentMesas);
            
            document.getElementById('loading-map').style.display = 'none';
        } catch (error) {
            console.error("Error fetching map:", error);
            document.getElementById('loading-map').innerHTML = '<p style="color:red">Error cargando mapa</p>';
        }
    }

    function renderMesas(mesas) {
        mesas.forEach(mesa => {
            const isSelected = selectedMesaId === mesa.id;
            const fillColor = getColorByStatus(isSelected ? 'seleccionada' : mesa.status);
            
            const group = new Konva.Group({
                id: `mesa-${mesa.id}`,
                x: mesa.pos_x,
                y: mesa.pos_y,
                rotation: mesa.rotacion,
                draggable: config.isAdmin, // Only admin can drag
            });

            // Determine shape
            let shape;
            if (mesa.tipo_mesa === 'redonda') {
                shape = new Konva.Circle({ radius: mesa.ancho / 2 });
            } else if (mesa.tipo_mesa === 'barra') {
                shape = new Konva.Rect({ width: mesa.ancho * 2, height: mesa.alto * 0.6, cornerRadius: 5 });
            } else { // cuadrada/rectangular
                shape = new Konva.Rect({ width: mesa.ancho, height: mesa.alto, cornerRadius: 5 });
            }

            shape.fill(fillColor);
            shape.stroke(isSelected ? '#fff' : '#0f0f1a');
            shape.strokeWidth(isSelected ? 3 : 1);
            
            // Adjust offset for center rotation
            if (mesa.tipo_mesa !== 'redonda') {
                shape.offsetX(shape.width() / 2);
                shape.offsetY(shape.height() / 2);
            }

            const label = new Konva.Text({
                text: mesa.numero_mesa ? `${mesa.numero_mesa}` : mesa.nombre,
                fontSize: 16,
                fontFamily: 'Inter',
                fontStyle: 'bold',
                fill: '#fff',
                align: 'center',
            });
            label.offsetX(label.width() / 2);
            label.offsetY(label.height() / 2);

            group.add(shape, label);
            
            // Interaction
            if (!config.isAdmin && mesa.status === 'disponible') {
                group.on('mouseenter', () => {
                    document.body.style.cursor = 'pointer';
                    shape.shadowColor('#10b981');
                    shape.shadowBlur(15);
                    showTooltip(mesa, group);
                });
                group.on('mouseleave', () => {
                    document.body.style.cursor = 'default';
                    shape.shadowBlur(0);
                    hideTooltip();
                });
                group.on('click tap', () => {
                    selectMesa(mesa);
                });
            } else if (config.isAdmin) {
                group.on('mouseenter', () => { document.body.style.cursor = 'move'; showTooltip(mesa, group); });
                group.on('mouseleave', () => { document.body.style.cursor = 'default'; hideTooltip(); });
                
                group.on('dragend', (e) => {
                    saveMesaPosition(mesa.id, e.target.x(), e.target.y(), e.target.rotation());
                });
            } else {
                // Not available and not admin
                group.on('mouseenter', () => { showTooltip(mesa, group); });
                group.on('mouseleave', () => { hideTooltip(); });
            }

            tablesLayer.add(group);
        });
        
        tablesLayer.draw();
    }

    function getColorByStatus(status) {
        switch(status) {
            case 'disponible': return '#10b981'; // Green
            case 'reservada': return '#ef4444'; // Red
            case 'bloqueada': return '#f59e0b'; // Gold/Yellow
            case 'seleccionada': return '#3b82f6'; // Blue
            case 'inactiva': return '#6b7280'; // Gray
            default: return '#6b7280';
        }
    }

    function showTooltip(mesa, group) {
        let text = `${mesa.nombre} (Cap: ${mesa.capacidad})`;
        if (mesa.status !== 'disponible' && !config.isAdmin) {
            text += `\nEstado: ${mesa.status}`;
        }
        
        tooltipText.text(text);
        tooltipBg.width(tooltipText.width());
        tooltipBg.height(tooltipText.height());
        
        const pos = group.getAbsolutePosition();
        tooltipGroup.position({
            x: pos.x + 20,
            y: pos.y - tooltipBg.height() - 10
        });
        
        tooltipGroup.to({ opacity: 1, duration: 0.2 });
        tooltipLayer.draw();
    }

    function hideTooltip() {
        tooltipGroup.to({ opacity: 0, duration: 0.2 });
    }

    function selectMesa(mesa) {
        selectedMesaId = mesa.id;
        
        // Re-render to show selection styling
        tablesLayer.destroyChildren();
        renderMesas(currentMesas);
        
        // Update UI panels
        document.getElementById('empty-selection').style.display = 'none';
        document.getElementById('selected-table-info').style.display = 'block';
        
        document.getElementById('info-nombre').innerText = mesa.nombre;
        document.getElementById('info-zona').innerText = mesa.zona_nombre || 'Principal';
        document.getElementById('info-capacidad').innerText = `${mesa.capacidad} personas`;
        
        // Bind reserve button
        const btn = document.getElementById('btn-reserve');
        btn.onclick = () => processHoldAndReserve(mesa.id);
    }

    async function processHoldAndReserve(mesaId) {
        const btn = document.getElementById('btn-reserve');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btn.disabled = true;
        
        const fecha = document.getElementById('search-fecha').value;
        const horaInicio = document.getElementById('search-hora-inicio').value;
        const horaFin = document.getElementById('search-hora-fin').value;

        try {
            const response = await axios.post(config.apiHoldUrl.replace('{id}', mesaId), {
                fecha: fecha,
                hora_inicio: horaInicio,
                hora_fin: horaFin
            });
            
            if (response.data.success) {
                // Redirect to reservation form with the hold secured
                const url = new URL(config.reserveUrl.replace(':id', mesaId), window.location.origin);
                url.searchParams.append('fecha', fecha);
                url.searchParams.append('hora_inicio', horaInicio);
                url.searchParams.append('hora_fin', horaFin);
                window.location.href = url.toString();
            }
        } catch (error) {
            alert(error.response?.data?.message || 'Error al intentar reservar la mesa. Quizás alguien más la acaba de tomar.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            fetchDisponibilidad(); // refresh map
        }
    }
    
    async function saveMesaPosition(id, x, y, rotation) {
        try {
            await axios.put(`/api/mesas/${id}/position`, {
                pos_x: x,
                pos_y: y,
                rotacion: rotation
            });
        } catch(e) {
            console.error('Error saving position', e);
        }
    }

    // Bind events
    document.getElementById('btn-search')?.addEventListener('click', fetchDisponibilidad);
    
    // Initial fetch
    fetchDisponibilidad();
    
    // Window resize handling
    window.addEventListener('resize', () => {
        stage.width(container.offsetWidth);
    });

    // TODO: Connect Laravel Echo here when broadcasting is enabled
    if (window.Echo) {
        window.Echo.channel('restaurant-map')
            .listen('.mesa.status.changed', (e) => {
                // Auto refresh map when a table status changes globally
                if (e.fecha === document.getElementById('search-fecha').value) {
                    fetchDisponibilidad();
                }
            });
    }
}
