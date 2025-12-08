<?php
namespace CentralTickets\Client;

use CentralTickets\Ticket;
use CentralTickets\Placeholders\PlaceholderEngineTicket;
use CentralTickets\Placeholders\PlaceholderEnginePassenger;

use CentralTickets\Components\Component;
use CentralTickets\Components\TextComponent;
use CentralTickets\Components\CompositeComponent;

use CentralTickets\Persistence\TicketRepository;

class TicketViewer implements Component
{
    private Ticket $ticket;
    private string $ticket_template;
    private string $ticket_js_template;
    private string $ticket_css_template;
    private string $passenger_template;

    public function __construct($ticket_id)
    {
        $ticket = (new TicketRepository)->find($ticket_id);
        if ($ticket !== null) {
            $this->ticket = $ticket;
        }

        $this->ticket_js_template = git_get_map_setting('ticket_viewer.viewer_js', '');
        $this->ticket_css_template = git_get_map_setting('ticket_viewer.viewer_css', '');
        $this->ticket_template = git_get_map_setting('ticket_viewer.ticket_viewer_html', '');
        $this->passenger_template = git_get_map_setting('ticket_viewer.passenger_viewer_html', '');
    }

    public function compact()
    {
        if (empty($this->ticket)) {
            return (new TicketViewerNotAvailable)->compact();
        }
        $container = new CompositeComponent;
        $container->add_child($this->card());
        return $container->compact();
    }

    private function replace_placeholder_ticket(string $template)
    {
        $engine = new PlaceholderEngineTicket($this->ticket);
        $result = $engine->process($template);

        foreach ($this->ticket->get_passengers() as $passenger) {
            $passenger_engine = new PlaceholderEnginePassenger($passenger);
            $result .= $passenger_engine->process($this->passenger_template);
        }

        return git_string_to_component($result);
    }

    private function card()
    {
        $result = $this->replace_placeholder_ticket($this->ticket_template)->compact();
        $result .= (new TextComponent('style', $this->ticket_css_template))->compact();
        $result .= (new TextComponent('script', $this->ticket_js_template))->compact();
        return git_string_to_component($result);
    }
}
