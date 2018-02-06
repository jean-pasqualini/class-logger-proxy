<?php
$data = json_decode(base64_decode($_SERVER['argv'][1]), true);
$data = ($data) ? $data : [];

$method = $data;

    $arguments = [];
    foreach ($method['parameters'] as $parameter) {
        $argument = sprintf('%s$%s', $parameter['type'], $parameter['name']);
        $arguments[] = $argument.' = null';
    }
    $arguments = implode(', ', $arguments);

    $argumentsNamed = [];
    foreach ($method['parameters'] as $parameter) {
        $argumentsNamed[] = sprintf('$%s', $parameter['name']);
    }
    $argumentsNamed = implode(', ', $argumentsNamed);
    ?>
        $body = function(<?php echo $arguments; ?>) {
        <?php echo $method['body']; ?>
        };

        $returnValue = $body(<?php echo $argumentsNamed; ?>);
        $this->_proxy_postCall('<?php echo $method['name']; ?>', [<?php echo $argumentsNamed; ?>], $returnValue);

        return $returnValue;