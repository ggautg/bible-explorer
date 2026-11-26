// resources/js/composables/useConstellationGraph.js
import { router } from '@inertiajs/vue3';
import { drag } from 'd3-drag';
import {
    forceCenter,
    forceCollide,
    forceLink,
    forceManyBody,
    forceSimulation,
    forceX,
    forceY,
} from 'd3-force';
import { select } from 'd3-selection';
import { zoom, zoomIdentity } from 'd3-zoom';

export function useConstellationGraph(svgRef, selected) {
    function reference(n) {
        return `${n.book} ${n.chapter}:${n.verse}`;
    }

    function navigateTo(n) {
        router.visit(`/bible/${n.book}/${n.chapter}/${n.verse}`, {
            preserveScroll: false,
        });
    }

    function render(nodesData, edgesData) {
        const width = 900;
        const height = 640;

        const svg = select(svgRef.value);
        svg.selectAll('*').remove();

        // Grupo contenedor: todo lo que dibujemos va DENTRO de este <g>,
        // así el zoom transforma todo junto (líneas + nodos) de una sola vez
        const container = svg.append('g');

        const zoomBehavior = zoom()
            .scaleExtent([0.5, 4]) // no deja alejarse más de 0.5x ni acercarse más de 4x
            .on('zoom', (event) => {
                container.attr('transform', event.transform);
            });

        svg.call(zoomBehavior);

        // Zoom inicial un poco más cercano que el default (opcional, ajustable)
        svg.call(
            zoomBehavior.transform,
            zoomIdentity.scale(1.3).translate(-100, -75),
        );

        const nodes = nodesData.map((n) => ({ ...n }));
        const links = edgesData.map((e) => ({ ...e }));

        const simulation = forceSimulation(nodes)
            .force(
                'link',
                forceLink(links)
                    .id((d) => d.id)
                    .distance((d) => 200 - (d.score || 40)),
            )
            .force('charge', forceManyBody().strength(-260))
            .force('center', forceCenter(width / 2, height / 2))
            .force('collide', forceCollide(38))
            .force('x', forceX(width / 2).strength(0.04))
            .force('y', forceY(height / 2).strength(0.04))
            .stop();

        simulation.tick(300);

        nodes.forEach((d) => {
            d.x = Math.max(20, Math.min(width - 20, d.x));
            d.y = Math.max(20, Math.min(height - 20, d.y));
        });

        const link = container
            .append('g')
            .selectAll('line')
            .data(links)
            .join('line')
            .attr('stroke', '#C9A227')
            .attr('stroke-opacity', (d) => 0.15 + ((d.score || 30) / 100) * 0.5)
            .attr('stroke-width', (d) => 0.6 + ((d.score || 30) / 100) * 1.8)
            .attr('x1', (d) => d.source.x)
            .attr('y1', (d) => d.source.y)
            .attr('x2', (d) => d.target.x)
            .attr('y2', (d) => d.target.y);

        const node = container
            .append('g')
            .selectAll('g')
            .data(nodes)
            .join('g')
            .attr('transform', (d) => `translate(${d.x},${d.y})`)
            .attr('tabindex', 0)
            .attr('role', 'button')
            .attr('aria-label', (d) => `Ver ${reference(d)}`)
            .style('cursor', 'pointer')
            .call(
                drag()
                    .on('start', dragstarted)
                    .on('drag', dragged)
                    .on('end', dragended),
            )
            .on('click', (event, d) => {
                selected.value = d;
            })
            .on('dblclick', (event, d) => navigateTo(d))
            .on('keydown', (event, d) => {
                if (event.key === 'Enter') {
                    navigateTo(d);
                } else if (event.key === ' ') {
                    event.preventDefault();
                    selected.value = d;
                }
            });

        node.append('circle')
            .attr('r', (d) => (d.isCenter ? 14 : 7))
            .attr('fill', (d) => (d.isCenter ? '#E8C87A' : '#8A7B54'))
            .attr('stroke', '#0B0E1A')
            .attr('stroke-width', 1.5);

        node.append('text')
            .text((d) => reference(d))
            .attr('x', 12)
            .attr('y', 4)
            .attr('fill', '#8F8874')
            .attr('font-family', 'Inter, sans-serif')
            .attr('font-size', (d) => (d.isCenter ? '13px' : '11px'));

        simulation.on('tick', () => {
            nodes.forEach((d) => {
                d.x = Math.max(20, Math.min(width - 20, d.x));
                d.y = Math.max(20, Math.min(height - 20, d.y));
            });

            link.attr('x1', (d) => d.source.x)
                .attr('y1', (d) => d.source.y)
                .attr('x2', (d) => d.target.x)
                .attr('y2', (d) => d.target.y);

            node.attr('transform', (d) => `translate(${d.x},${d.y})`);
        });

        function dragstarted(event, d) {
            if (!event.active) {
                simulation.alphaTarget(0.3).restart();
            }

            d.fx = d.x;
            d.fy = d.y;
        }
        function dragged(event, d) {
            d.fx = event.x;
            d.fy = event.y;
        }
        function dragended(event, d) {
            if (!event.active) {
                simulation.alphaTarget(0);
            }

            d.fx = null;
            d.fy = null;
        }
    }

    return { render, reference, navigateTo };
}
