#!lua name=dialoglib

redis.register_function('delete_user_keys', function(keys, args)
    local prefix = args[1]
    local cursor = "0"
    local total_deleted = 0

    repeat
        local result = redis.call('SCAN', cursor, 'MATCH', prefix .. '*', 'COUNT', 1000)
        cursor = result[1]
        local batch = result[2]

        if #batch > 0 then
            redis.call('DEL', unpack(batch))
            total_deleted = total_deleted + #batch
        end
    until cursor == "0"

    return total_deleted
end)


